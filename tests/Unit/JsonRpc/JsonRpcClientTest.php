<?php

declare(strict_types=1);

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

describe('JsonRpcClient', function () {
    it('can be instantiated with transport', function () {
        $transport = Mockery::mock(Transport::class);

        $client = new JsonRpcClient($transport);

        expect($client)->toBeInstanceOf(JsonRpcClient::class)
            ->and($client->isRunning())->toBeFalse();
    });

    it('start method calls transport start', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $client = new JsonRpcClient($transport);
        $client->start();

        expect($client->isRunning())->toBeTrue();
    });

    it('stop method stops client', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $client = new JsonRpcClient($transport);
        $client->start();
        $client->stop();

        expect($client->isRunning())->toBeFalse();
    });

    it('request sends message and waits for response', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $sentMessage = null;
        $transport->shouldReceive('send')->once()->andReturnUsing(function ($message) use (&$sentMessage) {
            $sentMessage = $message;
        });

        // Return a response matching the request id
        $transport->shouldReceive('tryRead')->andReturnUsing(function () use (&$sentMessage) {
            // Extract request id from sent message
            if (preg_match('/\{.*\}/s', $sentMessage, $matches)) {
                $request = json_decode($matches[0], true);
                $response = JsonRpcMessage::response($request['id'], ['status' => 'ok']);

                return $response->toJson();
            }

            return '';
        });

        $client = new JsonRpcClient($transport);
        $client->start();

        $result = $client->request('ping', ['message' => 'test']);

        expect($result)->toBe(['status' => 'ok']);
    });

    it('request throws JsonRpcException on error response', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $sentMessage = null;
        $transport->shouldReceive('send')->once()->andReturnUsing(function ($message) use (&$sentMessage) {
            $sentMessage = $message;
        });

        $transport->shouldReceive('tryRead')->andReturnUsing(function () use (&$sentMessage) {
            if (preg_match('/\{.*\}/s', $sentMessage, $matches)) {
                $request = json_decode($matches[0], true);
                $response = JsonRpcMessage::errorResponse($request['id'], -32600, 'Invalid Request');

                return $response->toJson();
            }

            return '';
        });

        $client = new JsonRpcClient($transport);
        $client->start();

        expect(fn () => $client->request('bad.method'))
            ->toThrow(JsonRpcException::class, 'Invalid Request');
    });

    it('request throws JsonRpcException on timeout', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('send')->once();
        $transport->shouldReceive('tryRead')->andReturn('');

        $client = new JsonRpcClient($transport);
        $client->start();

        expect(fn () => $client->request('test.method', [], 0.2))
            ->toThrow(JsonRpcException::class, 'Timeout waiting for response');
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
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $receivedMethod = null;
        $receivedParams = null;

        $transport->shouldReceive('tryRead')->andReturn(
            JsonRpcMessage::notification('test.notification', ['key' => 'value'])->toJson(),
            '',
        );

        $client = new JsonRpcClient($transport);
        $client->setNotificationHandler(function ($method, $params) use (&$receivedMethod, &$receivedParams) {
            $receivedMethod = $method;
            $receivedParams = $params;
        });

        $client->start();
        $client->processMessages(0.15);

        expect($receivedMethod)->toBe('test.notification')
            ->and($receivedParams)->toBe(['key' => 'value']);
    });

    it('setRequestHandler handles incoming requests', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        // Mock incoming request from server
        $transport->shouldReceive('tryRead')->andReturn(
            JsonRpcMessage::request('server-req-1', 'client.getInfo', ['type' => 'version'])->toJson(),
            '',
        );

        $sentResponses = [];
        $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentResponses) {
            $sentResponses[] = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->setRequestHandler('client.getInfo', function ($params) {
            return ['version' => '1.0.0', 'type' => $params['type']];
        });

        $client->start();
        $client->processMessages(0.15);

        expect($sentResponses)->toHaveCount(1)
            ->and($sentResponses[0])->toContain('"id":"server-req-1"')
            ->and($sentResponses[0])->toContain('"result"');
    });

    it('removeRequestHandler removes handler', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        // Mock incoming request from server
        $transport->shouldReceive('tryRead')->andReturn(
            JsonRpcMessage::request('server-req-1', 'client.getInfo', [])->toJson(),
            '',
        );

        $sentResponses = [];
        $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentResponses) {
            $sentResponses[] = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->setRequestHandler('client.getInfo', fn () => ['version' => '1.0.0']);
        $client->removeRequestHandler('client.getInfo');

        $client->start();
        $client->processMessages(0.15);

        // Should return error since handler was removed
        expect($sentResponses)->toHaveCount(1)
            ->and($sentResponses[0])->toContain('"error"')
            ->and($sentResponses[0])->toContain('Method not found');
    });

    it('handles request handler exception as error response', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $transport->shouldReceive('tryRead')->andReturn(
            JsonRpcMessage::request('server-req-1', 'client.failing', [])->toJson(),
            '',
        );

        $sentResponses = [];
        $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentResponses) {
            $sentResponses[] = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->setRequestHandler('client.failing', function () {
            throw new RuntimeException('Handler failed');
        });

        $client->start();
        $client->processMessages(0.15);

        expect($sentResponses)->toHaveCount(1)
            ->and($sentResponses[0])->toContain('"error"')
            ->and($sentResponses[0])->toContain('Handler failed');
    });

    it('handles JsonRpcException from handler with specific code', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $transport->shouldReceive('tryRead')->andReturn(
            JsonRpcMessage::request('server-req-1', 'client.custom', [])->toJson(),
            '',
        );

        $sentResponses = [];
        $transport->shouldReceive('send')->andReturnUsing(function ($message) use (&$sentResponses) {
            $sentResponses[] = $message;
        });

        $client = new JsonRpcClient($transport);
        $client->setRequestHandler('client.custom', function () {
            throw new JsonRpcException(-32001, 'Custom error', ['detail' => 'extra']);
        });

        $client->start();
        $client->processMessages(0.15);

        expect($sentResponses)->toHaveCount(1)
            ->and($sentResponses[0])->toContain('"code":-32001')
            ->and($sentResponses[0])->toContain('Custom error');
    });

    it('processMessages handles notifications while waiting', function () {
        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $notifications = [];
        $transport->shouldReceive('tryRead')->andReturn(
            JsonRpcMessage::notification('event.first', ['seq' => 1])->toJson(),
            JsonRpcMessage::notification('event.second', ['seq' => 2])->toJson(),
            '',
        );

        $client = new JsonRpcClient($transport);
        $client->setNotificationHandler(function ($method, $params) use (&$notifications) {
            $notifications[] = ['method' => $method, 'params' => $params];
        });

        $client->start();
        $client->processMessages(0.15);

        expect($notifications)->toHaveCount(2)
            ->and($notifications[0]['method'])->toBe('event.first')
            ->and($notifications[1]['method'])->toBe('event.second');
    });
});

describe('JsonRpcClient with preventStrayRequests', function () {
    it('throws StrayRequestException when requests are prevented', function () {
        Copilot::preventStrayRequests();

        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('send')->never();
        $transport->shouldReceive('tryRead')->never();

        $client = new JsonRpcClient($transport);
        $client->start();

        expect(fn () => $client->request('test.method'))
            ->toThrow(StrayRequestException::class);
    });

    it('allows specific methods when configured', function () {
        Copilot::preventStrayRequests(allow: ['ping']);

        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();

        $sentMessage = null;
        $transport->shouldReceive('send')->once()->andReturnUsing(function ($message) use (&$sentMessage) {
            $sentMessage = $message;
        });

        $transport->shouldReceive('tryRead')->andReturnUsing(function () use (&$sentMessage) {
            if (preg_match('/\{.*\}/s', $sentMessage, $matches)) {
                $request = json_decode($matches[0], true);
                $response = JsonRpcMessage::response($request['id'], ['message' => 'pong']);

                return $response->toJson();
            }

            return '';
        });

        $client = new JsonRpcClient($transport);
        $client->start();

        $result = $client->request('ping');

        expect($result)->toBe(['message' => 'pong']);
    });

    it('still throws for non-allowed methods', function () {
        Copilot::preventStrayRequests(allow: ['ping']);

        $transport = Mockery::mock(Transport::class);
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('send')->never();
        $transport->shouldReceive('tryRead')->never();

        $client = new JsonRpcClient($transport);
        $client->start();

        expect(fn () => $client->request('session.create'))
            ->toThrow(StrayRequestException::class);
    });
});
