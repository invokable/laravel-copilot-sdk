<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\NamedProviderConfig;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

function clientWithMockRpc(JsonRpcClient $rpc): Client
{
    $client = new Client;
    (new ReflectionProperty(Client::class, 'state'))->setValue($client, ConnectionState::CONNECTED);
    (new ReflectionProperty(Client::class, 'rpcClient'))->setValue($client, $rpc);

    return $client;
}

function startClientWithProviderTokenHandler(
    JsonRpcClient $rpc,
    array &$handlers,
    array &$requests,
    array $responses = [],
): Client {
    $processManager = Mockery::mock(ProcessManager::class);
    $processManager->shouldReceive('start')->once();
    $processManager->shouldReceive('getStdioTransport')
        ->once()
        ->andReturn(Mockery::mock(StdioTransport::class));

    app()->bind(ProcessManager::class, fn () => $processManager);
    app()->bind(JsonRpcClient::class, fn () => $rpc);

    $rpc->shouldReceive('start')->once();
    $rpc->shouldReceive('setNotificationHandler')->once();
    $rpc->shouldReceive('setRequestHandler')
        ->times(12)
        ->andReturnUsing(function (string $method, Closure $handler) use (&$handlers): void {
            $handlers[$method] = $handler;
        });
    $rpc->shouldReceive('request')
        ->andReturnUsing(function (string $method, array $params) use (&$requests, $responses): array {
            $requests[$method][] = $params;
            $response = $responses[$method] ?? null;

            if ($response instanceof Throwable) {
                throw $response;
            }

            if (is_callable($response)) {
                return $response($params);
            }

            return match ($method) {
                'connect' => ['version' => '', 'protocolVersion' => Protocol::version()],
                'session.create', 'session.resume' => ['sessionId' => $params['sessionId']],
                'session.detach' => ['success' => true],
                default => [],
            };
        });

    $client = new Client;
    $client->start();

    return $client;
}

describe('session provider lifecycle', function () {
    it('rejects skill providers for cloud sessions before issuing a create request', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldNotReceive('request');
        $client = clientWithMockRpc($rpc);

        expect(fn () => $client->createSession([
            'cloud' => [],
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'skillProvider' => [
                'listSkills' => fn () => [],
                'readSkill' => fn () => null,
            ],
        ]))->toThrow(InvalidArgumentException::class, 'Skill providers are not supported for cloud sessions');
    });

    it('removes pre-registered provider sessions when create fails', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->once()
            ->with('session.create', Mockery::type('array'))
            ->andThrow(new JsonRpcException(-32000, 'create failed'));
        $client = clientWithMockRpc($rpc);

        expect(fn () => $client->createSession([
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'skillProvider' => [
                'listSkills' => fn () => [],
                'readSkill' => fn () => null,
            ],
        ]))->toThrow(JsonRpcException::class, 'create failed');

        expect((new ReflectionProperty(Client::class, 'sessions'))->getValue($client))->toBe([]);
    });

    it('sends singular provider callback flags and returns its token until disconnect', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];
        $callbackArgs = null;
        $client = startClientWithProviderTokenHandler($rpc, $handlers, $requests);
        $callback = function (array $args) use (&$callbackArgs): string {
            $callbackArgs = $args;

            return 'managed-identity-token';
        };
        $session = $client->createSession(new SessionConfig(
            sessionId: 'session-1',
            onPermissionRequest: PermissionHandler::approveAll(),
            provider: new ProviderConfig(
                baseUrl: 'https://api.example.com',
                bearerTokenProvider: $callback,
            ),
        ));

        expect($requests['session.create'][0]['provider'])->toMatchArray([
            'baseUrl' => 'https://api.example.com',
            'hasBearerTokenProvider' => true,
        ])
            ->and($requests['session.create'][0]['provider'])->not->toHaveKey('bearerTokenProvider')
            ->and($handlers['providerToken.getToken']([
                'sessionId' => 'session-1',
                'providerName' => 'default',
            ]))->toBe(['token' => 'managed-identity-token'])
            ->and($callbackArgs)->toBe([
                'providerName' => 'default',
                'sessionId' => 'session-1',
            ]);

        $session->disconnect();

        expect(fn () => $handlers['providerToken.getToken']([
            'sessionId' => 'session-1',
            'providerName' => 'default',
        ]))->toThrow(RuntimeException::class, 'No bearer-token provider registered for provider "default"');
    });

    it('routes named provider token requests to their callback', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];

        $client = startClientWithProviderTokenHandler($rpc, $handlers, $requests);
        $session = $client->createSession([
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'providers' => [
                new NamedProviderConfig(
                    name: 'azure',
                    baseUrl: 'https://api.example.com',
                    bearerTokenProvider: fn (array $args): string => $args['providerName'].'-token',
                ),
            ],
        ]);

        expect($handlers['providerToken.getToken']([
            'sessionId' => $session->id(),
            'providerName' => 'azure',
        ]))->toBe(['token' => 'azure-token']);
    });

    it('sends singular provider callback flags when resuming a session', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];
        $client = startClientWithProviderTokenHandler($rpc, $handlers, $requests);
        $session = $client->resumeSession('session-2', new ResumeSessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            provider: new ProviderConfig(
                baseUrl: 'https://api.example.com',
                bearerTokenProvider: fn (array $args): string => 'resume-token',
            ),
        ));

        expect($requests['session.resume'][0]['provider'])->toMatchArray([
            'baseUrl' => 'https://api.example.com',
            'hasBearerTokenProvider' => true,
        ])
            ->and($requests['session.resume'][0]['provider'])->not->toHaveKey('bearerTokenProvider')
            ->and($handlers['providerToken.getToken']([
                'sessionId' => $session->id(),
                'providerName' => 'default',
            ]))->toBe(['token' => 'resume-token']);
    });

    it('returns token callback exceptions as request errors', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];
        $client = startClientWithProviderTokenHandler($rpc, $handlers, $requests);
        $session = $client->createSession([
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'provider' => new ProviderConfig(
                baseUrl: 'https://api.example.com',
                bearerTokenProvider: fn (): string => throw new RuntimeException('Token acquisition failed'),
            ),
        ]);

        expect(fn () => $handlers['providerToken.getToken']([
            'sessionId' => $session->id(),
            'providerName' => 'default',
        ]))->toThrow(RuntimeException::class, 'Token acquisition failed');
    });

    it('removes bearer token callbacks when create fails', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];

        $client = startClientWithProviderTokenHandler(
            $rpc,
            $handlers,
            $requests,
            ['session.create' => new JsonRpcException(-32000, 'create failed')],
        );

        expect(fn () => $client->createSession([
            'sessionId' => 'failed-session',
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'provider' => new ProviderConfig(
                baseUrl: 'https://api.example.com',
                bearerTokenProvider: fn (): string => 'token',
            ),
        ]))->toThrow(JsonRpcException::class, 'create failed')
            ->and(fn () => $handlers['providerToken.getToken']([
                'sessionId' => 'failed-session',
                'providerName' => 'default',
            ]))->toThrow(RuntimeException::class, 'No bearer-token provider registered for provider "default"');
    });

    it('removes bearer token callbacks when resume fails', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];

        $client = startClientWithProviderTokenHandler(
            $rpc,
            $handlers,
            $requests,
            ['session.resume' => new JsonRpcException(-32000, 'resume failed')],
        );

        expect(fn () => $client->resumeSession('failed-session', new ResumeSessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            provider: new ProviderConfig(
                baseUrl: 'https://api.example.com',
                bearerTokenProvider: fn (): string => 'token',
            ),
        )))->toThrow(JsonRpcException::class, 'resume failed')
            ->and(fn () => $handlers['providerToken.getToken']([
                'sessionId' => 'failed-session',
                'providerName' => 'default',
            ]))->toThrow(RuntimeException::class, 'No bearer-token provider registered for provider "default"');
    });
});
