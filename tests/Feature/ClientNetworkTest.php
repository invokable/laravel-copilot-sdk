<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\NetworkScope;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\Rpc\NetworkAuthenticateNegotiateRequest;
use Revolution\Copilot\Types\Rpc\NetworkResolveProxyRequest;

describe('Client network callbacks', function () {
    it('validates the network config', function () {
        $invalidConfigs = [
            'not-an-array',
            ['scope' => 'invalid', 'resolveProxy' => fn () => null],
            ['scope' => null, 'resolveProxy' => fn () => null],
            ['resolveProxy' => 'not-callable'],
            ['authenticateNegotiate' => 'not-callable'],
        ];

        foreach ($invalidConfigs as $config) {
            expect(fn () => new Client(['network' => $config]))
                ->toThrow(InvalidArgumentException::class);
        }
    });

    it('requires a runtime that supports the network registration RPC', function () {
        expect(fn () => startClientWithNetworkOptions(
            ['network' => ['authenticateNegotiate' => fn () => null]],
            new JsonRpcException(-32601, 'Method not found'),
        ))->toThrow(RuntimeException::class, 'network.setProvider');
    });

    it('registers only configured callbacks and their scope', function () {
        $result = startClientWithNetworkOptions([
            'network' => [
                'scope' => NetworkScope::Runtime,
                'resolveProxy' => fn () => 'DIRECT',
                'authenticateNegotiate' => fn () => 'spnego-token',
            ],
        ]);

        expect(array_column($result['requests'], 0))
            ->toBe(['connect', 'network.setProvider'])
            ->and($result['requests'][1][1])
            ->toEqual([
                'scope' => 'runtime',
                'resolveProxy' => (object) [],
                'authenticateNegotiate' => (object) [],
            ])
            ->and($result['handlers'])
            ->toHaveKeys(['network.resolveProxy', 'network.authenticateNegotiate']);
    });

    it('does not register when the network config has no callbacks', function () {
        $result = startClientWithNetworkOptions([
            'network' => ['scope' => 'connection'],
        ]);

        expect(array_column($result['requests'], 0))
            ->toBe(['connect'])
            ->and($result['handlers'])
            ->not->toHaveKey('network.resolveProxy')
            ->not->toHaveKey('network.authenticateNegotiate');
    });

    it('passes typed requests and cancellation tokens to both callbacks', function () {
        $receivedProxy = null;
        $receivedNegotiate = null;
        $result = startClientWithNetworkOptions([
            'network' => [
                'resolveProxy' => function (NetworkResolveProxyRequest $request, CancellationToken $token) use (&$receivedProxy): string {
                    $receivedProxy = [$request, $token];

                    return 'PROXY proxy.example:8080; DIRECT';
                },
                'authenticateNegotiate' => function (NetworkAuthenticateNegotiateRequest $request, CancellationToken $token) use (&$receivedNegotiate): string {
                    $receivedNegotiate = [$request, $token];

                    return 'spnego-token';
                },
            ],
        ]);
        $token = new CancellationToken;

        $proxy = ($result['handlers']['network.resolveProxy'])(
            ['url' => 'https://api.example/'],
            $token,
        );
        $negotiate = ($result['handlers']['network.authenticateNegotiate'])(
            ['spn' => 'HTTP/proxy.example'],
            $token,
        );

        expect($receivedProxy[0])
            ->toBeInstanceOf(NetworkResolveProxyRequest::class)
            ->and($receivedProxy[0]->url)->toBe('https://api.example/')
            ->and($receivedProxy[1])->toBe($token)
            ->and($proxy)->toBe(['proxy' => 'PROXY proxy.example:8080; DIRECT']);

        expect($receivedNegotiate[0])
            ->toBeInstanceOf(NetworkAuthenticateNegotiateRequest::class)
            ->and($receivedNegotiate[0]->spn)->toBe('HTTP/proxy.example')
            ->and($receivedNegotiate[1])->toBe($token)
            ->and($negotiate)->toBe(['token' => 'spnego-token']);
    });

    it('omits null callback results', function () {
        $result = startClientWithNetworkOptions([
            'network' => [
                'resolveProxy' => fn () => null,
                'authenticateNegotiate' => fn () => null,
            ],
        ]);

        $proxy = ($result['handlers']['network.resolveProxy'])(
            ['url' => 'https://api.example/'],
            new CancellationToken,
        );
        $negotiate = ($result['handlers']['network.authenticateNegotiate'])(
            ['spn' => 'HTTP/proxy.example'],
            new CancellationToken,
        );

        expect($proxy)->toBeInstanceOf(stdClass::class)
            ->and(json_encode($proxy))->toBe('{}')
            ->and($negotiate)->toBeInstanceOf(stdClass::class)
            ->and(json_encode($negotiate))->toBe('{}');
    });

    it('rejects invalid callback results and propagates callback exceptions', function () {
        $invalidResultClient = startClientWithNetworkOptions([
            'network' => ['resolveProxy' => fn () => 123],
        ]);

        expect(fn () => ($invalidResultClient['handlers']['network.resolveProxy'])(
            ['url' => 'https://api.example/'],
            new CancellationToken,
        ))->toThrow(UnexpectedValueException::class, 'network.resolveProxy');

        $invalidTokenClient = startClientWithNetworkOptions([
            'network' => ['authenticateNegotiate' => fn () => 123],
        ]);

        expect(fn () => ($invalidTokenClient['handlers']['network.authenticateNegotiate'])(
            ['spn' => 'HTTP/proxy.example'],
            new CancellationToken,
        ))->toThrow(UnexpectedValueException::class, 'network.authenticateNegotiate');

        $throwingClient = startClientWithNetworkOptions([
            'network' => [
                'authenticateNegotiate' => fn () => throw new RuntimeException('credential provider failed'),
            ],
        ]);

        expect(fn () => ($throwingClient['handlers']['network.authenticateNegotiate'])(
            ['spn' => 'HTTP/proxy.example'],
            new CancellationToken,
        ))->toThrow(RuntimeException::class, 'credential provider failed');
    });
});

/**
 * @return array{
 *     client: Client,
 *     handlers: array<string, Closure>,
 *     requests: list<array{string, array}>
 * }
 */
function startClientWithNetworkOptions(array $options, ?JsonRpcException $registrationFailure = null): array
{
    $handlers = [];
    $requests = [];
    $network = $options['network'] ?? null;
    $networkOptionSet = $network !== null;
    $resolveProxyConfigured = $networkOptionSet && is_callable($network['resolveProxy'] ?? null);
    $authenticateNegotiateConfigured = $networkOptionSet
        && is_callable($network['authenticateNegotiate'] ?? null);

    $processManager = Mockery::mock(ProcessManager::class);
    $processManager->shouldReceive('start')->once();
    $processManager->shouldReceive('getStdioTransport')
        ->once()
        ->andReturn(Mockery::mock(StdioTransport::class));

    $rpcClient = Mockery::mock(JsonRpcClient::class);
    $rpcClient->shouldReceive('start')->once();
    $rpcClient->shouldReceive('setNotificationHandler')->once();
    $rpcClient->shouldReceive('setRequestHandler')
        ->withArgs(function (string $method, Closure $handler) use (&$handlers): bool {
            $handlers[$method] = $handler;

            return true;
        })
        ->times(34 + (int) $resolveProxyConfigured + (int) $authenticateNegotiateConfigured);
    $rpcClient->shouldReceive('request')
        ->andReturnUsing(function (string $method, array $params) use (&$requests, $registrationFailure): array {
            $requests[] = [$method, $params];

            if ($method === 'network.setProvider' && $registrationFailure !== null) {
                throw $registrationFailure;
            }

            return $method === 'connect' ? ['protocolVersion' => Protocol::version()] : [];
        });

    app()->bind(ProcessManager::class, fn () => $processManager);
    app()->bind(JsonRpcClient::class, fn () => $rpcClient);

    $client = new Client($options);
    $client->start();

    return [
        'client' => $client,
        'handlers' => $handlers,
        'requests' => $requests,
    ];
}
