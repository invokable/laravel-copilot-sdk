<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\Psr7\Stream as PsrStream;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Revolt\EventLoop;
use Revolution\Copilot\Client;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Support\CopilotRequestContext;
use Revolution\Copilot\Support\CopilotRequestHandler;
use Revolution\Copilot\Transport\StdioTransport;

describe('Client request handler', function () {
    it('validates request handler options and aliases', function () {
        foreach (['requestHandler', 'request_handler'] as $option) {
            expect(fn () => new Client([$option => new stdClass]))
                ->toThrow(InvalidArgumentException::class, 'requestHandler must be a CopilotRequestHandler or Closure');
        }

        expect(new Client(['request_handler' => fn () => null]))->toBeInstanceOf(Client::class);
    });

    it('registers handlers and the provider, then forwards split request bodies', function () {
        $received = null;
        $handler = function (CopilotRequestContext $context, string $method, string $body) use (&$received): Response {
            $received = [$context, $method, $body];

            return new Response(Http::psr7Response('response', 201, ['Content-Type' => 'text/plain']));
        };

        $result = startClientWithRequestHandler($handler);

        expect(array_column($result['requests'], 0))->toBe(['connect', 'llmInference.setProvider'])
            ->and(array_keys($result['handlers']))->toContain(
                'llmInference.httpRequestStart',
                'llmInference.httpRequestChunk',
            );

        expect(($result['handlers']['llmInference.httpRequestStart'])([
            'headers' => ['X-Model' => ['test']],
            'method' => 'POST',
            'requestId' => 'request-1',
            'url' => 'https://model.test/chat',
            'sessionId' => 'session-1',
            'agentId' => 'agent-1',
            'agentInvocationId' => 'invocation-1',
            'parentAgentId' => 'parent-1',
            'interactionType' => 'chat',
        ]))->toBe([]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => 'body-',
            'requestId' => 'request-1',
        ]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => base64_encode("\x00\xff"),
            'requestId' => 'request-1',
            'binary' => true,
            'end' => true,
        ]);

        EventLoop::run();

        expect($received[0])->toBeInstanceOf(CopilotRequestContext::class)
            ->and($received[0]->requestId)->toBe('request-1')
            ->and($received[0]->sessionId)->toBe('session-1')
            ->and($received[0]->agentId)->toBe('agent-1')
            ->and($received[0]->agentInvocationId)->toBe('invocation-1')
            ->and($received[0]->parentAgentId)->toBe('parent-1')
            ->and($received[0]->interactionType)->toBe('chat')
            ->and($received[1])->toBe('POST')
            ->and($received[2])->toBe("body-\x00\xff");

        $responseStart = findRequest($result['requests'], 'llmInference.httpResponseStart');
        $responseChunks = findRequests($result['requests'], 'llmInference.httpResponseChunk');

        expect($responseStart[1]['status'])->toBe(201)
            ->and($responseChunks[0][1])->toMatchArray(['data' => 'response', 'requestId' => 'request-1'])
            ->and($responseChunks[1][1])->toMatchArray(['data' => '', 'end' => true, 'requestId' => 'request-1']);
    });

    it('buffers binary chunks that arrive before the request start', function () {
        $body = null;
        $result = startClientWithRequestHandler(
            function (CopilotRequestContext $context, string $method, string $requestBody) use (&$body): Response {
                $body = $requestBody;

                return new Response(Http::psr7Response(''));
            },
        );

        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => base64_encode("\xffA"),
            'requestId' => 'request-early',
            'binary' => true,
        ]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => 'B',
            'requestId' => 'request-early',
            'end' => true,
        ]);
        ($result['handlers']['llmInference.httpRequestStart'])([
            'headers' => [],
            'method' => 'POST',
            'requestId' => 'request-early',
            'url' => 'https://model.test/chat',
        ]);

        EventLoop::run();

        expect($body)->toBe("\xffAB");
    });

    it('uses the default handler as a streaming HTTP pass-through', function () {
        Http::fake([
            'https://model.test/*' => Http::response("\x00\xff", 200, ['Content-Type' => 'application/octet-stream']),
        ]);

        $result = startClientWithRequestHandler(new CopilotRequestHandler);

        ($result['handlers']['llmInference.httpRequestStart'])([
            'headers' => ['X-Model' => ['original']],
            'method' => 'POST',
            'requestId' => 'request-default',
            'url' => 'https://model.test/chat',
        ]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => 'payload',
            'requestId' => 'request-default',
            'end' => true,
        ]);

        EventLoop::run();

        $recorded = Http::recorded()->first()[0];
        $responseChunks = findRequests($result['requests'], 'llmInference.httpResponseChunk');

        expect($recorded->method())->toBe('POST')
            ->and($recorded->url())->toBe('https://model.test/chat')
            ->and($recorded->body())->toBe('payload')
            ->and($recorded->header('X-Model'))->toBe(['original'])
            ->and($responseChunks[0][1])->toMatchArray([
                'data' => base64_encode("\x00\xff"),
                'binary' => true,
                'requestId' => 'request-default',
            ]);
    });

    it('forwards modified URLs and headers from a handler subclass', function () {
        Http::fake([
            'https://proxy.test/*' => Http::response('proxied'),
        ]);
        $handler = new class extends CopilotRequestHandler
        {
            public function sendRequest(CopilotRequestContext $context, string $method, string $body): Response
            {
                $context->url = 'https://proxy.test/chat';
                $context->headers['X-Request-Source'] = ['laravel'];

                return parent::sendRequest($context, $method, $body);
            }
        };
        $result = startClientWithRequestHandler($handler);

        ($result['handlers']['llmInference.httpRequestStart'])([
            'headers' => [],
            'method' => 'POST',
            'requestId' => 'request-proxy',
            'url' => 'https://model.test/chat',
        ]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => 'payload',
            'requestId' => 'request-proxy',
            'end' => true,
        ]);

        EventLoop::run();

        $request = Http::recorded()->first()[0];

        expect($request->url())->toBe('https://proxy.test/chat')
            ->and($request->header('X-Request-Source'))->toBe(['laravel']);
    });

    it('returns 499 on cancellation before the request body completes', function () {
        $handlerCalled = false;
        $result = startClientWithRequestHandler(
            function (CopilotRequestContext $context, string $method, string $body) use (&$handlerCalled): Response {
                $handlerCalled = true;

                return new Response(Http::psr7Response('unexpected'));
            },
        );

        ($result['handlers']['llmInference.httpRequestStart'])([
            'headers' => [],
            'method' => 'POST',
            'requestId' => 'request-cancelled',
            'url' => 'https://model.test/chat',
        ]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => '',
            'requestId' => 'request-cancelled',
            'cancel' => true,
        ]);

        EventLoop::run();

        $responseStart = findRequest($result['requests'], 'llmInference.httpResponseStart');
        $responseChunk = findRequest($result['requests'], 'llmInference.httpResponseChunk');

        expect($handlerCalled)->toBeFalse()
            ->and($responseStart[1]['status'])->toBe(499)
            ->and($responseChunk[1]['error']['code'])->toBe('cancelled')
            ->and($responseChunk[1]['end'])->toBeTrue();
    });

    it('interrupts a response stream when the runtime cancels the request', function () {
        [$responseStream, $responseWriter] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $cancelObserved = false;
        $result = startClientWithRequestHandler(
            function (CopilotRequestContext $context) use ($responseStream, &$cancelObserved): Response {
                $context->signal->onCancellationRequested(function () use (&$cancelObserved): void {
                    $cancelObserved = true;
                });

                return new Response(new PsrResponse(200, [], new PsrStream($responseStream)));
            },
        );

        ($result['handlers']['llmInference.httpRequestStart'])([
            'headers' => [],
            'method' => 'POST',
            'requestId' => 'request-stream-cancel',
            'url' => 'https://model.test/chat',
        ]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => '',
            'requestId' => 'request-stream-cancel',
            'end' => true,
        ]);
        EventLoop::delay(0.01, function () use (&$result): void {
            ($result['handlers']['llmInference.httpRequestChunk'])([
                'data' => '',
                'requestId' => 'request-stream-cancel',
                'cancel' => true,
            ]);
        });

        EventLoop::run();
        fclose($responseWriter);

        $responseStart = findRequest($result['requests'], 'llmInference.httpResponseStart');
        $responseChunk = findRequest($result['requests'], 'llmInference.httpResponseChunk');

        expect($cancelObserved)->toBeTrue()
            ->and($responseStart[1]['status'])->toBe(200)
            ->and($responseChunk[1]['error']['code'])->toBe('cancelled')
            ->and($responseChunk[1]['end'])->toBeTrue();
    });

    it('returns 502 when the configured handler throws', function () {
        $result = startClientWithRequestHandler(
            fn () => throw new RuntimeException('upstream failed'),
        );

        ($result['handlers']['llmInference.httpRequestStart'])([
            'headers' => [],
            'method' => 'POST',
            'requestId' => 'request-failed',
            'url' => 'https://model.test/chat',
        ]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => '',
            'requestId' => 'request-failed',
            'end' => true,
        ]);

        EventLoop::run();

        $responseStart = findRequest($result['requests'], 'llmInference.httpResponseStart');
        $responseChunk = findRequest($result['requests'], 'llmInference.httpResponseChunk');

        expect($responseStart[1]['status'])->toBe(502)
            ->and($responseChunk[1]['error']['message'])->toBe('upstream failed')
            ->and($responseChunk[1]['end'])->toBeTrue();
    });

    it('returns an explicit error for unsupported WebSocket transport', function () {
        $result = startClientWithRequestHandler(fn () => new Response(Http::psr7Response('not used')));

        ($result['handlers']['llmInference.httpRequestStart'])([
            'headers' => [],
            'method' => 'GET',
            'requestId' => 'request-websocket',
            'url' => 'wss://model.test/socket',
            'transport' => 'websocket',
        ]);
        ($result['handlers']['llmInference.httpRequestChunk'])([
            'data' => '',
            'requestId' => 'request-websocket',
            'end' => true,
        ]);

        EventLoop::run();

        $responseStart = findRequest($result['requests'], 'llmInference.httpResponseStart');
        $responseChunk = findRequest($result['requests'], 'llmInference.httpResponseChunk');

        expect($responseStart[1]['status'])->toBe(501)
            ->and($responseChunk[1]['error']['code'])->toBe('unsupported_transport')
            ->and($responseChunk[1]['end'])->toBeTrue();
    });

    it('does not register or activate the provider without a request handler', function () {
        $result = startClientWithRequestHandler(null);

        expect(array_column($result['requests'], 0))->toBe(['connect'])
            ->and($result['handlers'])->not->toHaveKey('llmInference.httpRequestStart')
            ->and($result['handlers'])->not->toHaveKey('llmInference.httpRequestChunk');
    });
});

/**
 * @return array{
 *     client: Client,
 *     handlers: array<string, Closure>,
 *     requests: list<array{string, array}>
 * }
 */
function startClientWithRequestHandler(CopilotRequestHandler|Closure|null $handler): array
{
    $handlers = [];
    $requests = [];

    $processManager = Mockery::mock(ProcessManager::class);
    $processManager->shouldReceive('start')->once();
    $processManager->shouldReceive('getStdioTransport')
        ->once()
        ->andReturn(Mockery::mock(StdioTransport::class));

    $rpcClient = Mockery::mock(JsonRpcClient::class);
    $rpcClient->shouldReceive('start')->once();
    $rpcClient->shouldReceive('setNotificationHandler')->once();
    $rpcClient->shouldReceive('setRequestHandler')
        ->withArgs(function (string $method, Closure $callback) use (&$handlers): bool {
            $handlers[$method] = $callback;

            return true;
        })
        ->times($handler === null ? 29 : 31);
    $rpcClient->shouldReceive('request')
        ->andReturnUsing(function (string $method, array $params) use (&$requests): array {
            $requests[] = [$method, $params];

            return match ($method) {
                'connect' => ['protocolVersion' => Protocol::version()],
                'llmInference.setProvider' => ['success' => true],
                'llmInference.httpResponseStart',
                'llmInference.httpResponseChunk' => ['accepted' => true],
                default => [],
            };
        });

    app()->bind(ProcessManager::class, fn () => $processManager);
    app()->bind(JsonRpcClient::class, fn () => $rpcClient);

    $client = new Client(array_filter([
        'requestHandler' => $handler,
    ], fn ($value) => $value !== null));
    $client->start();

    return [
        'client' => $client,
        'handlers' => $handlers,
        'requests' => &$requests,
    ];
}

/**
 * @param  list<array{string, array}>  $requests
 * @return array{string, array}
 */
function findRequest(array $requests, string $method): array
{
    foreach ($requests as $request) {
        if ($request[0] === $method) {
            return $request;
        }
    }

    throw new RuntimeException("Request not found: {$method}");
}

/**
 * @param  list<array{string, array}>  $requests
 * @return list<array{string, array}>
 */
function findRequests(array $requests, string $method): array
{
    return array_values(array_filter(
        $requests,
        fn (array $request): bool => $request[0] === $method,
    ));
}
