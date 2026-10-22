<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ExtensionSource;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveRequest;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveResult;

describe('Client extension launch provider', function () {
    it('registers the configured provider after protocol verification and resolves launch requests', function () {
        $receivedRequest = null;
        $provider = function (ExtensionLaunchProviderResolveRequest $request) use (&$receivedRequest): array {
            $receivedRequest = $request;

            return [
                'launch' => [
                    'executable' => '/usr/bin/node',
                    'args' => [$request->modulePath],
                    'env' => ['EXTENSION_SOURCE' => $request->source->value],
                ],
            ];
        };

        $result = startClientWithExtensionLaunchProvider(['extensionLaunchProvider' => $provider]);

        expect(array_column($result['requests'], 0))
            ->toBe(['connect', 'registerExtensionLaunchProvider']);

        expect($result['requests'][1])->toBe(['registerExtensionLaunchProvider', []]);

        $launch = ($result['handlers']['extensionLaunchProvider.resolve'])([
            'id' => 'project:example',
            'name' => 'example',
            'modulePath' => '/extensions/example/index.js',
            'source' => 'project',
        ]);

        expect($receivedRequest)->toBeInstanceOf(ExtensionLaunchProviderResolveRequest::class)
            ->and($receivedRequest->source)->toBe(ExtensionSource::PROJECT)
            ->and($launch)->toBe([
                'launch' => [
                    'executable' => '/usr/bin/node',
                    'args' => ['/extensions/example/index.js'],
                    'env' => ['EXTENSION_SOURCE' => 'project'],
                ],
            ]);
    });

    it('returns no launch and skips registration when no provider is configured', function () {
        $result = startClientWithExtensionLaunchProvider([]);

        $launch = ($result['handlers']['extensionLaunchProvider.resolve'])([
            'id' => 'project:example',
            'name' => 'example',
            'modulePath' => '/extensions/example/index.js',
            'source' => 'project',
        ]);

        expect(array_column($result['requests'], 0))->toBe(['connect'])
            ->and($launch)->toBe([]);
    });

    it('returns no launch when the provider returns null or throws', function () {
        $provider = function (ExtensionLaunchProviderResolveRequest $request): ?ExtensionLaunchProviderResolveResult {
            if ($request->name === 'throws') {
                throw new RuntimeException('provider failed');
            }

            return null;
        };

        $result = startClientWithExtensionLaunchProvider(['extension_launch_provider' => $provider]);
        $resolve = $result['handlers']['extensionLaunchProvider.resolve'];

        expect($resolve([
            'id' => 'project:null',
            'name' => 'null',
            'modulePath' => '/extensions/null/index.js',
            'source' => 'project',
        ]))->toBe([])
            ->and($resolve([
                'id' => 'project:throws',
                'name' => 'throws',
                'modulePath' => '/extensions/throws/index.js',
                'source' => 'project',
            ]))->toBe([]);
    });
});

/**
 * @return array{
 *     client: Client,
 *     handlers: array<string, Closure>,
 *     requests: list<array{string, array}>
 * }
 */
function startClientWithExtensionLaunchProvider(array $options): array
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
        ->withArgs(function (string $method, Closure $handler) use (&$handlers): bool {
            $handlers[$method] = $handler;

            return true;
        })
        ->times(31);
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
