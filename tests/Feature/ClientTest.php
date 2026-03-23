<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Events\Session\ResumeSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\CommandDefinition;
use Revolution\Copilot\Types\ModelInfo;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    // Don't use preventStrayRequests here as Client tests use mocked RPC client
});

describe('Client', function () {
    it('can be instantiated with options', function () {
        $client = new Client([
            'cli_path' => '/test/copilot',
            'log_level' => 'debug',
        ]);

        expect($client)->toBeInstanceOf(Client::class)
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED);
    });

    it('creates ProcessManager via app container', function () {
        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->never(); // Not started yet

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);

        $client = new Client(['cli_path' => '/test/copilot']);

        expect($client->getState())->toBe(ConnectionState::DISCONNECTED);
    });

    it('start method connects to server', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();

        expect($client->getState())->toBe(ConnectionState::CONNECTED);

        fclose($stdin);
        fclose($stdout);
    });

    it('start method is idempotent when already connected', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();
        $client->start(); // Second call should be a no-op

        expect($client->getState())->toBe(ConnectionState::CONNECTED);

        fclose($stdin);
        fclose($stdout);
    });

    it('throws when protocol version mismatch', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => 1]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;

        expect(fn () => $client->start())
            ->toThrow(RuntimeException::class, 'SDK protocol version mismatch');

        fclose($stdin);
        fclose($stdout);
    });

    it('stop method cleans up resources', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('stop')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('stop')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();
        $errors = $client->stop();

        expect($errors)->toBe([])
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED);

        fclose($stdin);
        fclose($stdout);
    });

    it('createSession creates and returns session', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::any())
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once();
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession([
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);

        expect($session)->toBe($mockSession);

        fclose($stdin);
        fclose($stdout);
    });

    it('createSession sends only name/description for array commands in RPC request', function () {
        $handler = fn ($ctx) => 'result';
        $commands = [
            [
                'name' => 'test',
                'description' => 'Test command',
                'handler' => $handler,
            ],
        ];

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::on(function ($params) {
                $cmds = $params['commands'] ?? null;

                return is_array($cmds)
                    && count($cmds) === 1
                    && $cmds[0] === ['name' => 'test', 'description' => 'Test command']
                    && ! array_key_exists('handler', $cmds[0]);
            }))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with($commands);
        $mockSession->shouldReceive('setCapabilities')->once()->with(null);
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession([
            'commands' => $commands,
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);

        expect($session)->toBe($mockSession);
    });

    it('createSession with CommandDefinition objects sends only name/description to RPC and passes originals to registerCommands', function () {
        $handler = fn ($ctx) => 'result';
        $commands = [
            new CommandDefinition(
                name: 'deploy',
                handler: $handler,
                description: 'Deploy the application',
            ),
        ];

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::on(function ($params) {
                $cmds = $params['commands'] ?? null;

                return is_array($cmds)
                    && count($cmds) === 1
                    && $cmds[0] === ['name' => 'deploy', 'description' => 'Deploy the application']
                    && ! array_key_exists('handler', $cmds[0]);
            }))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with($commands);
        $mockSession->shouldReceive('setCapabilities')->once()->with(null);
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession([
            'commands' => $commands,
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);

        expect($session)->toBe($mockSession);
    });

    it('createSession passes capabilities payload from response to setCapabilities', function () {
        $capabilitiesPayload = ['ui' => ['elicitation' => true]];

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::any())
            ->once()
            ->andReturn([
                'sessionId' => 'test-session-123',
                'capabilities' => $capabilitiesPayload,
            ]);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once()->with($capabilitiesPayload);
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession([
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);

        expect($session)->toBe($mockSession);
    });

    it('resumeSession sends only name/description for commands in RPC request', function () {
        Event::fake();

        $handler = fn ($ctx) => 'result';
        $commands = [
            [
                'name' => 'rollback',
                'description' => 'Rollback last deployment',
                'handler' => $handler,
            ],
        ];

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.resume', Mockery::on(function ($params) {
                $cmds = $params['commands'] ?? null;

                return is_array($cmds)
                    && count($cmds) === 1
                    && $cmds[0] === ['name' => 'rollback', 'description' => 'Rollback last deployment']
                    && ! array_key_exists('handler', $cmds[0]);
            }))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with($commands);
        $mockSession->shouldReceive('setCapabilities')->once()->with(null);
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->resumeSession('test-session-123', [
            'commands' => $commands,
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);

        expect($session)->toBe($mockSession);
    });

    it('throws when createSession called without connection', function () {
        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->never();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);

        $client = new Client;

        expect(fn () => $client->createSession([
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]))->toThrow(RuntimeException::class, 'Client not connected');
    });

    it('throws when createSession called without onPermissionRequest', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();

        expect(fn () => $client->createSession([]))
            ->toThrow(InvalidArgumentException::class, 'onPermissionRequest handler is required');

        fclose($stdin);
        fclose($stdout);
    });

    it('throws when resumeSession called without onPermissionRequest', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();

        expect(fn () => $client->resumeSession('test-session-123', []))
            ->toThrow(InvalidArgumentException::class, 'onPermissionRequest handler is required');

        fclose($stdin);
        fclose($stdout);
    });

    it('resumeSession resume and returns session', function () {
        Event::fake();

        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(4);
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.resume', Mockery::any())
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once();
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->resumeSession('test-session-123', [
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);

        expect($session)->toBe($mockSession);

        Event::assertDispatched(ResumeSession::class);

        fclose($stdin);
        fclose($stdout);
    });

    it('usingListModels returns the client instance', function () {
        $client = new Client;

        expect($client->usingListModels(fn () => []))->toBe($client);
    });

    it('usingListModels with null clears the handler', function () {
        $client = new Client;
        $client->usingListModels(fn () => []);
        $client->usingListModels(null);

        expect($client->usingListModels(null))->toBe($client);
    });

    it('listModels calls custom handler set via usingListModels', function () {
        $client = new Client;
        $client->usingListModels(fn () => [
            [
                'id' => 'my-model',
                'name' => 'My Model',
                'capabilities' => [
                    'supports' => ['vision' => false],
                    'limits' => ['max_context_window_tokens' => 8192],
                ],
            ],
        ]);

        $models = $client->listModels();

        expect($models)->toHaveCount(1)
            ->and($models[0])->toBeInstanceOf(ModelInfo::class)
            ->and($models[0]->id)->toBe('my-model')
            ->and($models[0]->name)->toBe('My Model');
    });

    it('listModels returns empty array when custom handler returns empty', function () {
        $client = new Client;
        $client->usingListModels(fn () => []);

        $models = $client->listModels();

        expect($models)->toBe([]);
    });
});
