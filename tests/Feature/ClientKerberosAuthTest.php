<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\Rpc\KerberosAuthAcquireRequest;

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
        $result = startClientWithKerberosAuth([
            'kerberosAuth' => [
                'mode' => 'always',
                'scope' => 'runtime',
                'callback' => fn () => 'token',
            ],
        ]);

        expect(array_column($result['requests'], 0))
            ->toBe(['connect', 'kerberosAuth.setProvider'])
            ->and($result['requests'][1][1])
            ->toBe(['mode' => 'always', 'scope' => 'runtime'])
            ->and($result['handlers'])
            ->toHaveKey('kerberosAuth.acquire');
    });

    it('omits the default scope from registration and accepts the snake-case alias', function () {
        $result = startClientWithKerberosAuth([
            'kerberos_auth' => [
                'mode' => 'fallback',
                'callback' => fn () => null,
            ],
        ]);

        expect($result['requests'][1])
            ->toBe(['kerberosAuth.setProvider', ['mode' => 'fallback']])
            ->and($result['handlers'])
            ->toHaveKey('kerberosAuth.acquire');
    });

    it('does not register Kerberos RPCs when the option is absent', function () {
        $result = startClientWithKerberosAuth([]);

        expect(array_column($result['requests'], 0))
            ->toBe(['connect'])
            ->and($result['handlers'])
            ->not->toHaveKey('kerberosAuth.acquire');
    });

    it('passes the SPN and cancellation token to the callback and returns its token', function () {
        $received = null;
        $callback = function (KerberosAuthAcquireRequest $request, CancellationToken $token) use (&$received): string {
            $received = [$request, $token];

            return 'spnego-token';
        };
        $result = startClientWithKerberosAuth([
            'kerberosAuth' => ['mode' => 'always', 'callback' => $callback],
        ]);
        $cancellationToken = new CancellationToken;

        $response = ($result['handlers']['kerberosAuth.acquire'])(
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
        $result = startClientWithKerberosAuth([
            'kerberos_auth' => ['mode' => 'fallback', 'callback' => fn () => null],
        ]);

        expect(($result['handlers']['kerberosAuth.acquire'])(
            ['spn' => 'HTTP/service.example'],
            new CancellationToken,
        ))->toBe([]);
    });

    it('rejects callback results that are not strings or null', function () {
        $result = startClientWithKerberosAuth([
            'kerberosAuth' => ['mode' => 'fallback', 'callback' => fn () => 123],
        ]);

        expect(fn () => ($result['handlers']['kerberosAuth.acquire'])(
            ['spn' => 'HTTP/service.example'],
            new CancellationToken,
        ))->toThrow(UnexpectedValueException::class);
    });
});

/**
 * @return array{
 *     client: Client,
 *     handlers: array<string, Closure>,
 *     requests: list<array{string, array}>
 * }
 */
function startClientWithKerberosAuth(array $options): array
{
    $handlers = [];
    $requests = [];
    $kerberosAuthConfigured = ($options['kerberosAuth'] ?? $options['kerberos_auth'] ?? null) !== null;

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
        ->times(32 + (int) $kerberosAuthConfigured);
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
