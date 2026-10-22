<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Session;
use Revolution\Copilot\Transport\TcpTransport;
use Revolution\Copilot\Types\EntraAuthConfig;
use Revolution\Copilot\Types\RuntimeConnection;

beforeEach(function () {
    Copilot::clearResolvedInstances();
});

describe('Client TCP Mode', function () {
    it('can be instantiated with cli_url option', function () {
        $client = new Client([
            'cli_url' => 'tcp://127.0.0.1:12345',
        ]);

        expect($client)->toBeInstanceOf(Client::class)
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED)
            ->and($client->isTcpMode())->toBeTrue();
    });

    it('is not tcp mode when cli_url is not set', function () {
        $client = new Client([
            'cli_path' => '/test/copilot',
        ]);

        expect($client->isTcpMode())->toBeFalse();
    });

    it('starts a TCP connection with the host Entra registration', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(29);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', [
                'supportedTaskKinds' => ['agent', 'client', 'shell'],
                'entra' => ['clientId' => 'host-application-id'],
            ])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        // Use reflection to inject the mock transport
        $client = new Client([
            'cli_url' => 'tcp://127.0.0.1:12345',
            'entra' => new EntraAuthConfig(clientId: 'host-application-id'),
        ]);
        $reflection = new ReflectionClass($client);
        $transportProperty = $reflection->getProperty('transport');
        $transportProperty->setValue($client, $mockTransport);

        $client->start();

        expect($client->getState())->toBe(ConnectionState::CONNECTED)
            ->and($client->isTcpMode())->toBeTrue();
    });

    it('fails instead of falling back to ping when Entra is configured on a legacy runtime', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();
        $mockTransport->shouldReceive('stop')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('stop')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(29);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', [
                'supportedTaskKinds' => ['agent', 'client', 'shell'],
                'entra' => ['clientId' => 'host-application-id'],
            ])
            ->once()
            ->andThrow(new JsonRpcException(-32601, 'Unhandled method connect'));
        $mockRpcClient->shouldReceive('request')
            ->with('ping', [])
            ->never();
        $mockRpcClient->shouldReceive('request')
            ->with('runtime.shutdown', [], 10.0)
            ->never();

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client([
            'cli_url' => 'tcp://127.0.0.1:12345',
            'entra' => ['clientId' => 'host-application-id'],
        ]);
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('transport')->setValue($client, $mockTransport);

        expect(fn () => $client->start())
            ->toThrow(RuntimeException::class, 'This runtime does not support Entra client configuration.');

        expect($client->getState())->toBe(ConnectionState::ERROR);
    });

    it('falls back to ping for a legacy runtime when Entra is not configured', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();
        $mockTransport->shouldReceive('stop')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('stop')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(29);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andThrow(new JsonRpcException(-32601, 'Unhandled method connect'));
        $mockRpcClient->shouldReceive('request')
            ->with('ping', [], 10.0)
            ->once()
            ->andReturn(['protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('runtime.shutdown', [], 10.0)
            ->never();

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client(['cli_url' => 'tcp://127.0.0.1:12345']);
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('transport')->setValue($client, $mockTransport);

        $client->start();
        $client->stop();

        expect($client->getState())->toBe(ConnectionState::DISCONNECTED);
    });

    it('registers built-in plugin directories with an external server', function () {
        $paths = ['C:\\copilot\\plugins'];
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(29);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('plugins.builtin.set', ['paths' => $paths])
            ->once()
            ->andReturn([]);

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client([
            'connection' => RuntimeConnection::forUri('tcp://127.0.0.1:12345'),
            'builtinPluginDirectories' => $paths,
        ]);
        $reflection = new ReflectionClass($client);
        $transportProperty = $reflection->getProperty('transport');
        $transportProperty->setValue($client, $mockTransport);

        $client->start();

        expect($client->getState())->toBe(ConnectionState::CONNECTED)
            ->and($client->isTcpMode())->toBeTrue();
    });

    it('stop method closes TCP transport', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();
        $mockTransport->shouldReceive('stop')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('stop')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(29);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('runtime.shutdown', [], 10.0)
            ->never();

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client(['cli_url' => 'tcp://127.0.0.1:12345']);
        $reflection = new ReflectionClass($client);
        $transportProperty = $reflection->getProperty('transport');
        $transportProperty->setValue($client, $mockTransport);

        $client->start();
        $errors = $client->stop();

        expect($errors)->toBe([])
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED);
    });

    it('closes the TCP connection after a failed handshake without shutting down the server', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();
        $mockTransport->shouldReceive('stop')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('stop')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(29);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andThrow(new JsonRpcException(-32000, 'Connection failed'));
        $mockRpcClient->shouldReceive('request')
            ->with('runtime.shutdown', [], 10.0)
            ->never();

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client(['cli_url' => 'tcp://127.0.0.1:12345']);
        $reflection = new ReflectionClass($client);
        $transportProperty = $reflection->getProperty('transport');
        $transportProperty->setValue($client, $mockTransport);

        expect(fn () => $client->start())
            ->toThrow(JsonRpcException::class, 'Connection failed');
        expect($client->getState())->toBe(ConnectionState::ERROR);
    });

    it('returns TCP transport cleanup errors without shutting down the server', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();
        $mockTransport->shouldReceive('stop')->once()->andThrow(new RuntimeException('TCP close failed'));

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('stop')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(29);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('runtime.shutdown', [], 10.0)
            ->never();

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client(['cli_url' => 'tcp://127.0.0.1:12345']);
        $reflection = new ReflectionClass($client);
        $transportProperty = $reflection->getProperty('transport');
        $transportProperty->setValue($client, $mockTransport);

        $client->start();
        $errors = $client->stop();

        expect($errors)->toHaveCount(1)
            ->and($errors[0]->getMessage())->toBe('TCP close failed')
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED);
    });

    it('sends connection token during connect handshake', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(29);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['token' => 'secret', 'supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['ok' => true, 'version' => '', 'protocolVersion' => Protocol::version()]);

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client([
            'connection' => RuntimeConnection::forUri('tcp://127.0.0.1:12345', 'secret'),
        ]);
        $reflection = new ReflectionClass($client);
        $transportProperty = $reflection->getProperty('transport');
        $transportProperty->setValue($client, $mockTransport);

        $client->start();

        expect($client->getState())->toBe(ConnectionState::CONNECTED);
    });

    it('does not send the removed GitHub cache setting over TCP for create and resume', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->with('session.create', Mockery::on(fn (array $params) => ! array_key_exists('enableGitHubCacheStore', $params)))
            ->once()
            ->andReturn(['sessionId' => 'created-session']);
        $rpc->shouldReceive('request')
            ->with('session.resume', Mockery::on(fn (array $params) => ! array_key_exists('enableGitHubCacheStore', $params)))
            ->once()
            ->andReturn(['sessionId' => 'resumed-session']);

        $session = Mockery::mock(Session::class);
        $session->shouldReceive('registerTools')->twice()->with([]);
        $session->shouldReceive('registerCommands')->twice()->with([]);
        $session->shouldReceive('registerPermissionHandler')->twice();
        $session->shouldReceive('setWorkspacePath')->twice()->with(null);
        $session->shouldReceive('setCapabilities')->twice()->with(null);
        $session->shouldReceive('registerSkillProvider')->twice()->with(null);
        $session->shouldReceive('registerSessionFsProvider')->twice()->with(null);
        $this->app->bind(Session::class, fn () => $session);

        $client = new Client(['cli_url' => 'tcp://127.0.0.1:12345']);
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('rpcClient')->setValue($client, $rpc);
        $reflection->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $permissionHandler = static fn () => ['kind' => 'approve-once'];
        $created = $client->createSession([
            'onPermissionRequest' => $permissionHandler,
            'enableGitHubCacheStore' => true,
        ]);
        $resumed = $client->resumeSession('resumed-session', [
            'onPermissionRequest' => $permissionHandler,
            'enableGitHubCacheStore' => true,
        ]);

        expect($client->isTcpMode())->toBeTrue()
            ->and($created)->toBe($session)
            ->and($resumed)->toBe($session);
    });

    it('throws when github_token is used with cli_url', function () {
        expect(fn () => new Client([
            'cli_url' => 'tcp://127.0.0.1:12345',
            'github_token' => 'gho_test_token',
        ]))->toThrow(InvalidArgumentException::class, 'github_token and use_logged_in_user cannot be used with RuntimeConnection::forUri()');
    });

    it('throws when use_logged_in_user is used with cli_url', function () {
        expect(fn () => new Client([
            'cli_url' => 'tcp://127.0.0.1:12345',
            'use_logged_in_user' => false,
        ]))->toThrow(InvalidArgumentException::class, 'github_token and use_logged_in_user cannot be used with RuntimeConnection::forUri()');
    });
});
