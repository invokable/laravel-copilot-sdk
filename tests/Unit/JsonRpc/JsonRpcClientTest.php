<?php

declare(strict_types=1);

use Revolt\EventLoop;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Exceptions\StrayRequestException;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\JsonRpc\JsonRpcMessage;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    Copilot::preventStrayRequests(false);
});

/**
 * Create a mock transport that captures the onReceive handler.
 *
 * @return array{transport: \Mockery\MockInterface|Transport, simulateReceive: Closure}
 */
function createMockTransport(): array
{
    $handler = null;
    $transport = Mockery::mock(Transport::class);
    $transport->shouldReceive('onReceive')->andReturnUsing(function ($callback) use (&$handler) {
        $handler = $callback;
    });

    $simulateReceive = function (string $content) use (&$handler) {
        if ($handler !== null) {
            EventLoop::queue(function () use ($handler, $content) {
                $handler($content);
            });
        }
    };

    return ['transport' => $transport, 'simulateReceive' => $simulateReceive];
}

describe('JsonRpcClient', function () {
    it('can be instantiated with transport', function () {
        $transport = Mockery::mock(Transport::class);

        $client = new JsonRpcClient($transport);

        expect($client)->toBeInstanceOf(JsonRpcClient::class)
            ->and($client->isRunning())->toBeFalse();
    });

    it('start method calls transport start', function () {
        ['transport' => $transport] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $client = new JsonRpcClient($transport);
        $client->start();

        expect($client->isRunning())->toBeTrue();
    });

    it('stop method stops client', function () {
        ['transport' => $transport] = createMockTransport();
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('stop')->once();

        $client = new JsonRpcClient($transport);
        $client->start();
        $client->stop();

        expect($client->isRunning())->toBeFalse();
    });

    it('request sends message and waits for response', function () {
        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $sentMessage = null;
        $transport->shouldReceive('send')->once()->andReturnUsing(function ($message) use (&$sentMessage, $simulateReceive) {
            $sentMessage = $message;
            // Simulate response after sending
            if (preg_match('/\{.*\}/s', $message, $matches)) {
                $request = json_decode($matches[0], true);
                $response = JsonRpcMessage::response($request['id'], ['status' => 'ok']);
                $simulateReceive($response->toJson());
            }
        });

        $client = new JsonRpcClient($transport);
        $client->start();

        $result = $client->request('ping', ['message' => 'test']);

        expect($result)->toBe(['status' => 'ok']);
    });

    it('request throws JsonRpcException on error response', function () {
        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $transport->shouldReceive('send')->once()->andReturnUsing(function ($message) use ($simulateReceive) {
            if (preg_match('/\{.*\}/s', $message, $matches)) {
                $request = json_decode($matches[0], true);
                $response = JsonRpcMessage::errorResponse($request['id'], -32600, 'Invalid Request');
                $simulateReceive($response->toJson());
            }
        });

        $client = new JsonRpcClient($transport);
        $client->start();

        expect(fn () => $client->request('bad.method'))
            ->toThrow(JsonRpcException::class, 'Invalid Request');
    });

    it('request throws JsonRpcException on timeout', function () {
        ['transport' => $transport] = createMockTransport();
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('send')->once();

        $client = new JsonRpcClient($transport);
        $client->start();

        expect(fn () => $client->request('test.method', [], 0.2))
            ->toThrow(JsonRpcException::class, 'Timeout 0.2s waiting for response');
    });

    it('notify sends message without waiting for response', function () {
        $transport = Mockery::mock(Transport::class);

        $sentMessage = null;
        $transport->shouldReceive('send')->once()->andReturnUsing(function ($message) use (&$sentMessage) {
            $sentMessage = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->notify('notification.method', ['data' => 'test']);

        // Verify notification was sent (no id in notification)
        expect($sentMessage)->toContain('"method":"notification.method"')
            ->and($sentMessage)->not->toContain('"id"');
    });

    it('setNotificationHandler registers handler', function () {
        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $receivedMethod = null;
        $receivedParams = null;

        $client = new JsonRpcClient($transport);
        $client->setNotificationHandler(function ($method, $params) use (&$receivedMethod, &$receivedParams) {
            $receivedMethod = $method;
            $receivedParams = $params;
        });

        $client->start();

        // Simulate notification from server
        $simulateReceive(JsonRpcMessage::notification('test.notification', ['key' => 'value'])->toJson());

        // Run the event loop briefly to process the queued callback
        EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
        EventLoop::run();

        expect($receivedMethod)->toBe('test.notification')
            ->and($receivedParams)->toBe(['key' => 'value']);
    });

    it('setRequestHandler handles incoming requests', function () {
        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $sentResponses = [];
        $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentResponses) {
            $sentResponses[] = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->setRequestHandler('client.getInfo', function ($params) {
            return ['version' => '1.0.0', 'type' => $params['type']];
        });

        $client->start();

        // Simulate request from server
        $simulateReceive(JsonRpcMessage::request('server-req-1', 'client.getInfo', ['type' => 'version'])->toJson());

        EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
        EventLoop::run();

        expect($sentResponses)->toHaveCount(1)
            ->and($sentResponses[0])->toContain('"id":"server-req-1"')
            ->and($sentResponses[0])->toContain('"result"');
    });

    it('removeRequestHandler removes handler', function () {
        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $sentResponses = [];
        $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentResponses) {
            $sentResponses[] = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->setRequestHandler('client.getInfo', fn () => ['version' => '1.0.0']);
        $client->removeRequestHandler('client.getInfo');

        $client->start();

        // Simulate request from server
        $simulateReceive(JsonRpcMessage::request('server-req-1', 'client.getInfo', [])->toJson());

        EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
        EventLoop::run();

        // Should return error since handler was removed
        expect($sentResponses)->toHaveCount(1)
            ->and($sentResponses[0])->toContain('"error"')
            ->and($sentResponses[0])->toContain('Method not found');
    });

    it('handles request handler exception as error response', function () {
        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $sentResponses = [];
        $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentResponses) {
            $sentResponses[] = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->setRequestHandler('client.failing', function () {
            throw new RuntimeException('Handler failed');
        });

        $client->start();

        // Simulate request from server
        $simulateReceive(JsonRpcMessage::request('server-req-1', 'client.failing', [])->toJson());

        EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
        EventLoop::run();

        expect($sentResponses)->toHaveCount(1)
            ->and($sentResponses[0])->toContain('"error"')
            ->and($sentResponses[0])->toContain('Handler failed');
    });

    it('handles JsonRpcException from handler with specific code', function () {
        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $sentResponses = [];
        $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentResponses) {
            $sentResponses[] = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->setRequestHandler('client.custom', function () {
            throw new JsonRpcException(-32001, 'Custom error', ['detail' => 'extra']);
        });

        $client->start();

        // Simulate request from server
        $simulateReceive(JsonRpcMessage::request('server-req-1', 'client.custom', [])->toJson());

        EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
        EventLoop::run();

        expect($sentResponses)->toHaveCount(1)
            ->and($sentResponses[0])->toContain('"code":-32001')
            ->and($sentResponses[0])->toContain('Custom error');
    });

    it('handles multiple notifications', function () {
        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $notifications = [];

        $client = new JsonRpcClient($transport);
        $client->setNotificationHandler(function ($method, $params) use (&$notifications) {
            $notifications[] = ['method' => $method, 'params' => $params];
        });

        $client->start();

        // Simulate multiple notifications from server
        $simulateReceive(JsonRpcMessage::notification('event.first', ['seq' => 1])->toJson());
        $simulateReceive(JsonRpcMessage::notification('event.second', ['seq' => 2])->toJson());

        EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
        EventLoop::run();

        expect($notifications)->toHaveCount(2)
            ->and($notifications[0]['method'])->toBe('event.first')
            ->and($notifications[1]['method'])->toBe('event.second');
    });
});

describe('JsonRpcClient with preventStrayRequests', function () {
    it('throws StrayRequestException when requests are prevented', function () {
        Copilot::preventStrayRequests();

        ['transport' => $transport] = createMockTransport();
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('send')->never();

        $client = new JsonRpcClient($transport);
        $client->start();

        expect(fn () => $client->request('test.method'))
            ->toThrow(StrayRequestException::class);
    });

    it('allows specific methods when configured', function () {
        Copilot::preventStrayRequests(allow: ['ping']);

        ['transport' => $transport, 'simulateReceive' => $simulateReceive] = createMockTransport();
        $transport->shouldReceive('start')->once();

        $transport->shouldReceive('send')->once()->andReturnUsing(function ($message) use ($simulateReceive) {
            if (preg_match('/\{.*\}/s', $message, $matches)) {
                $request = json_decode($matches[0], true);
                $response = JsonRpcMessage::response($request['id'], ['message' => 'pong']);
                $simulateReceive($response->toJson());
            }
        });

        $client = new JsonRpcClient($transport);
        $client->start();

        $result = $client->request('ping');

        expect($result)->toBe(['message' => 'pong']);
    });

    it('still throws for non-allowed methods', function () {
        Copilot::preventStrayRequests(allow: ['ping']);

        ['transport' => $transport] = createMockTransport();
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('send')->never();

        $client = new JsonRpcClient($transport);
        $client->start();

        expect(fn () => $client->request('session.create'))
            ->toThrow(StrayRequestException::class);
    });
});
