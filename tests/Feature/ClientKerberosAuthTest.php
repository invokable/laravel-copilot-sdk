<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\Rpc\KerberosAuthAcquireRequest;
use Revolution\Copilot\Types\Rpc\NetworkAuthenticateNegotiateRequest;
use Revolution\Copilot\Types\Rpc\NetworkResolveProxyRequest;

describe('Client Kerberos authentication', function () {
    it('validates Kerberos options for both aliases', function () {
        $invalidConfigs = [
            'not-an-array',
            ['mode' => 'invalid', 'callback' => fn () => null],
            ['mode' => 'fallback', 'scope' => 'invalid', 'callback' => fn () => null],
            ['mode' => 'fallback', 'scope' => null, 'callback' => fn () => null],
            ['mode' => 'fallback', 'callback' => 'not-callable'],
            ['mode' => 'fallback'],
        ];

        foreach (['kerberosAuth', 'kerberos_auth'] as $option) {
            foreach ($invalidConfigs as $config) {
                expect(fn () => new Client([$option => $config]))
                    ->toThrow(InvalidArgumentException::class);
            }
        }
    });

    it('registers the configured provider after protocol verification', function () {
        $result = startClientWithNetworkOptions([
            'kerberosAuth' => [
                'mode' => 'always',
                'scope' => 'runtime',
                'callback' => fn () => 'token',
            ],
        ]);

        expect(array_column($result['requests'], 0))
            ->toBe(['connect', 'network.setProvider'])
            ->and($result['requests'][1][1])
            ->toEqual([
                'scope' => 'runtime',
                'authenticateNegotiate' => (object) [],
            ])
            ->and($result['handlers'])
            ->toHaveKey('network.authenticateNegotiate');
    });

    it('omits the default scope from registration and accepts the snake-case alias', function () {
        $result = startClientWithNetworkOptions([
            'kerberos_auth' => [
                'mode' => 'fallback',
                'callback' => fn () => null,
            ],
        ]);

        expect($result['requests'][1])
            ->toEqual([
                'network.setProvider',
                ['authenticateNegotiate' => (object) []],
            ])
            ->and($result['handlers'])
            ->toHaveKey('network.authenticateNegotiate');
    });

    it('does not register Kerberos RPCs when the option is absent', function () {
        $result = startClientWithNetworkOptions([]);

        expect(array_column($result['requests'], 0))
            ->toBe(['connect'])
            ->and($result['handlers'])
            ->not->toHaveKey('network.authenticateNegotiate');
    });

    it('passes the SPN and cancellation token to the callback and returns its token', function () {
        $received = null;
        $callback = function (KerberosAuthAcquireRequest $request, CancellationToken $token) use (&$received): string {
            $received = [$request, $token];

            return 'spnego-token';
        };
        $result = startClientWithNetworkOptions([
            'kerberosAuth' => ['mode' => 'always', 'callback' => $callback],
        ]);
        $cancellationToken = new CancellationToken;

        $response = ($result['handlers']['network.authenticateNegotiate'])(
            ['spn' => 'HTTP/service.example'],
            $cancellationToken,
        );

        expect($received[0])
            ->toBeInstanceOf(KerberosAuthAcquireRequest::class)
            ->and($received[0]->spn)->toBe('HTTP/service.example')
            ->and($received[1])->toBe($cancellationToken)
            ->and($response)->toBe(['token' => 'spnego-token']);
    });

    it('omits a null token returned by the callback', function () {
        $result = startClientWithNetworkOptions([
            'kerberos_auth' => ['mode' => 'fallback', 'callback' => fn () => null],
        ]);

        expect(($result['handlers']['network.authenticateNegotiate'])(
            ['spn' => 'HTTP/service.example'],
            new CancellationToken,
        ))->toBe([]);
    });

    it('rejects callback results that are not strings or null', function () {
        $result = startClientWithNetworkOptions([
            'kerberosAuth' => ['mode' => 'fallback', 'callback' => fn () => 123],
        ]);

        expect(fn () => ($result['handlers']['network.authenticateNegotiate'])(
            ['spn' => 'HTTP/service.example'],
            new CancellationToken,
        ))->toThrow(UnexpectedValueException::class);
    });
});

describe('Client network callbacks', function () {
    it('validates network config and callback aliases', function () {
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

    it('registers only configured callbacks and their scope', function () {
        $result = startClientWithNetworkOptions([
            'network' => [
                'scope' => 'runtime',
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

        expect(($result['handlers']['network.resolveProxy'])(
            ['url' => 'https://api.example/'],
            new CancellationToken,
        ))->toBe([])
            ->and(($result['handlers']['network.authenticateNegotiate'])(
                ['spn' => 'HTTP/proxy.example'],
                new CancellationToken,
            ))->toBe([]);
    });

    it('rejects invalid callback results and propagates callback exceptions', function () {
        $invalidResultClient = startClientWithNetworkOptions([
            'network' => ['resolveProxy' => fn () => 123],
        ]);

        expect(fn () => ($invalidResultClient['handlers']['network.resolveProxy'])(
            ['url' => 'https://api.example/'],
            new CancellationToken,
        ))->toThrow(UnexpectedValueException::class, 'network.resolveProxy');

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

    it('prefers network callbacks over the legacy Kerberos option', function () {
        $legacyCalled = false;
        $result = startClientWithNetworkOptions([
            'kerberosAuth' => [
                'mode' => 'always',
                'callback' => function () use (&$legacyCalled): string {
                    $legacyCalled = true;

                    return 'legacy-token';
                },
            ],
            'network' => [
                'scope' => 'connection',
                'authenticateNegotiate' => fn () => 'network-token',
            ],
        ]);

        expect($result['requests'][1][0])->toBe('network.setProvider')
            ->and($result['requests'][1][1])->toEqual([
                'scope' => 'connection',
                'authenticateNegotiate' => (object) [],
            ])
            ->and(($result['handlers']['network.authenticateNegotiate'])(
                ['spn' => 'HTTP/proxy.example'],
                new CancellationToken,
            ))->toBe(['token' => 'network-token'])
            ->and($legacyCalled)->toBeFalse();
    });
});

/**
 * @return array{
 *     client: Client,
 *     handlers: array<string, Closure>,
 *     requests: list<array{string, array}>
 * }
 */
function startClientWithNetworkOptions(array $options): array
{
    $handlers = [];
    $requests = [];
    $network = $options['network'] ?? null;
    $legacyKerberosAuth = $options['kerberosAuth'] ?? $options['kerberos_auth'] ?? null;
    $networkOptionSet = $network !== null;
    $resolveProxyConfigured = $networkOptionSet && is_callable($network['resolveProxy'] ?? null);
    $authenticateNegotiateConfigured = $networkOptionSet
        ? is_callable($network['authenticateNegotiate'] ?? null)
        : $legacyKerberosAuth !== null;

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
        ->times(32 + (int) $resolveProxyConfigured + (int) $authenticateNegotiateConfigured);
    $rpcClient->shouldReceive('request')
        ->andReturnUsing(function (string $method, array $params) use (&$requests): array {
            $requests[] = [$method, $params];

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
