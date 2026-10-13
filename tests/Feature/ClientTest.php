<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Events\Session\ResumeSession;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\CanvasProviderIdentity;
use Revolution\Copilot\Types\CommandDefinition;
use Revolution\Copilot\Types\ImageGenerationConfig;
use Revolution\Copilot\Types\McpAuthRequest;
use Revolution\Copilot\Types\McpAuthToken;
use Revolution\Copilot\Types\MemoryConfiguration;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\NamedProviderConfig;
use Revolution\Copilot\Types\ProviderModelConfig;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\SessionLimitsConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\SessionHooks;
use Revolution\Copilot\Types\ToolSearchConfig;

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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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

    it('falls back to ping when connect handshake is unavailable', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andThrow(new JsonRpcException(-32601, 'Method not found'));
        $mockRpcClient->shouldReceive('request')
            ->with('ping', [], 10.0)
            ->once()
            ->andReturn(['message' => 'pong', 'timestamp' => time(), 'protocolVersion' => Protocol::version()]);

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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::on(fn ($params) => ($params['askUserVariant'] ?? null) === 'elicitation'
                && ($params['capi'] ?? null) === ['autoTier' => 'balance']
                && ($params['toolSearch'] ?? null) === ['enabled' => true, 'deferThreshold' => 12]
                && ($params['imageGeneration'] ?? null) === ['enabled' => true]
                && ($params['canvasProvider'] ?? null) === ['id' => 'host-canvas']
                && ($params['providers'] ?? null) === [['name' => 'openai', 'baseUrl' => 'https://api.example.com']]
                && ($params['models'] ?? null) === [['id' => 'gpt-custom', 'provider' => 'openai']]
                && ($params['memory'] ?? null) === ['enabled' => true]
                && ($params['featureFlags'] ?? null) === ['feature-a' => true]
                && ($params['expAssignments'] ?? null) === ['assignment' => 'treatment']
                && ($params['refreshCustomInstructions'] ?? null) === true
                && ($params['diagnostics'] ?? null) === ['sources' => ['mcp' => ['level' => 'debug']]]
                && ($params['name'] ?? null) === 'startup-check'
                && ! array_key_exists('requestMcpApps', $params)
                && ! array_key_exists('enableMcpApps', $params)))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once();
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            askUserVariant: 'elicitation',
            capi: ['autoTier' => 'balance'],
            toolSearch: new ToolSearchConfig(enabled: true, deferThreshold: 12),
            imageGeneration: new ImageGenerationConfig(enabled: true),
            canvasProvider: new CanvasProviderIdentity(id: 'host-canvas'),
            providers: [new NamedProviderConfig(name: 'openai', baseUrl: 'https://api.example.com')],
            models: [new ProviderModelConfig(id: 'gpt-custom', provider: 'openai')],
            memory: new MemoryConfiguration(enabled: true),
            featureFlags: ['feature-a' => true],
            expAssignments: ['assignment' => 'treatment'],
            refreshCustomInstructions: true,
            diagnostics: ['sources' => ['mcp' => ['level' => 'debug']]],
            enableMcpApps: false,
            name: 'startup-check',
        ));

        expect($session)->toBe($mockSession);

        fclose($stdin);
        fclose($stdout);
    });

    it('registers MCP OAuth interest and its handler after creating a session', function () {
        Event::fake();

        $handlerContext = null;
        $handler = function (McpAuthRequest $request, array $context) use (&$handlerContext): McpAuthToken {
            $handlerContext = [$request->serverName, $context];

            return new McpAuthToken('host-token');
        };

        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->with('session.create', Mockery::on(fn (array $params) => ! array_key_exists('onMcpAuthRequest', $params)))
            ->once()
            ->ordered()
            ->andReturn(['sessionId' => 'created-session']);
        $rpc->shouldReceive('request')
            ->with('session.eventLog.registerInterest', [
                'sessionId' => 'created-session',
                'eventType' => 'mcp.oauth_required',
            ])
            ->once()
            ->ordered()
            ->andReturn(['handle' => 'oauth-interest']);
        $rpc->shouldReceive('request')
            ->with('session.mcp.oauth.handlePendingRequest', [
                'requestId' => 'oauth-request',
                'result' => ['kind' => 'token', 'accessToken' => 'host-token'],
                'sessionId' => 'created-session',
            ])
            ->once()
            ->ordered()
            ->andReturn(['success' => true]);

        $client = new Client;
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('rpcClient')->setValue($client, $rpc);
        $reflection->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            onMcpAuthRequest: $handler,
        ));
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => [
                'requestId' => 'oauth-request',
                'serverName' => 'private-mcp',
                'serverUrl' => 'https://mcp.example.test',
                'reason' => 'initial',
            ],
        ]));

        expect($handlerContext)->toBe(['private-mcp', ['sessionId' => 'created-session']]);
    });

    it('registers MCP OAuth interest when resuming a session with a callback alias', function () {
        Event::fake();

        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->with('session.resume', Mockery::on(fn (array $params) => ! array_key_exists('onMcpAuthRequest', $params)))
            ->once()
            ->ordered()
            ->andReturn(['sessionId' => 'resumed-session']);
        $rpc->shouldReceive('request')
            ->with('session.eventLog.registerInterest', [
                'sessionId' => 'resumed-session',
                'eventType' => 'mcp.oauth_required',
            ])
            ->once()
            ->ordered()
            ->andReturn(['handle' => 'oauth-interest']);
        $rpc->shouldReceive('request')
            ->with('session.mcp.oauth.handlePendingRequest', [
                'requestId' => 'resume-oauth-request',
                'result' => ['kind' => 'cancelled'],
                'sessionId' => 'resumed-session',
            ])
            ->once()
            ->ordered()
            ->andReturn(['success' => true]);

        $client = new Client;
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('rpcClient')->setValue($client, $rpc);
        $reflection->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $session = $client->resumeSession('resumed-session', [
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'on_mcp_auth_request' => fn () => null,
        ]);
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => ['requestId' => 'resume-oauth-request'],
        ]));

        expect($session->id())->toBe('resumed-session');
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
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

    it('createSession sends beta9 tool precedence and applies option flags', function () {
        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::on(fn ($params) => ($params['availableTools'] ?? null) === ['builtin:*']
                && ($params['excludedTools'] ?? null) === ['mcp:github-delete_repository']
                && ($params['toolFilterPrecedence'] ?? null) === 'excluded'
                && ($params['customAgentsLocalOnly'] ?? null) === true
                && ! array_key_exists('skipCustomInstructions', $params)
                && ! array_key_exists('coauthorEnabled', $params)
                && ! array_key_exists('manageScheduleEnabled', $params)
                && ! array_key_exists('toolSearch', $params)
                && ! array_key_exists('providers', $params)
                && ! array_key_exists('models', $params)
                && ! array_key_exists('memory', $params)
                && ! array_key_exists('imageGeneration', $params)
                && ! array_key_exists('canvasProvider', $params)
                && ! array_key_exists('featureFlags', $params)
                && ! array_key_exists('expAssignments', $params)
                && ! array_key_exists('refreshCustomInstructions', $params)
                && ! array_key_exists('diagnostics', $params)
                && ! array_key_exists('requestMcpApps', $params)
                && ! array_key_exists('enableMcpApps', $params)))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);
        $mockRpcClient->shouldReceive('request')
            ->with('session.options.update', [
                'sessionId' => 'test-session-123',
                'skipCustomInstructions' => true,
                'customAgentsLocalOnly' => true,
                'coauthorEnabled' => false,
                'manageScheduleEnabled' => true,
            ])
            ->once()
            ->andReturn(['success' => true]);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once()->with(null);
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession([
            'availableTools' => ['builtin:*'],
            'excludedTools' => ['mcp:github-delete_repository'],
            'skipCustomInstructions' => true,
            'customAgentsLocalOnly' => true,
            'coauthorEnabled' => false,
            'manageScheduleEnabled' => true,
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);

        expect($session)->toBe($mockSession);
    });

    it('forwards custom-agent hooks separately from session callback hooks', function () {
        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);

        $customAgents = [
            [
                'name' => 'restricted-agent',
                'prompt' => 'Review tool usage.',
                'hooks' => [
                    'preToolUse' => [['permissionDecision' => 'deny']],
                ],
            ],
            [
                'name' => 'other-agent',
                'prompt' => 'Review documentation.',
            ],
        ];
        $sessionHooks = new SessionHooks(onPreToolUse: fn () => null);

        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::on(fn ($params) => ($params['customAgents'] ?? null) === $customAgents
                && ($params['hooks'] ?? null) === true))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once()->with(null);
        $mockSession->shouldReceive('registerPermissionHandler')->once();
        $mockSession->shouldReceive('registerHooks')->once()->with($sessionHooks->toArray());

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession(new SessionConfig(
            customAgents: $customAgents,
            hooks: $sessionHooks,
            onPermissionRequest: PermissionHandler::approveAll(),
        ));

        expect($session)->toBe($mockSession);
    });

    it('createSession forwards managed settings options to RPC and Session', function () {
        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::on(fn ($params) => ($params['enableManagedSettings'] ?? null) === true
                && ($params['enforceManagedModelDefaults'] ?? null) === true
                && ($params['managedSettings'] ?? null) === ['permissions' => ['deny' => ['Shell(rm *)']]]
                && ($params['enableFileChangeTracking'] ?? null) === true
                && ($params['disabledMcpServers'] ?? null) === ['github']))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once()->with(null);
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $capturedParameters = null;

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, function ($app, $parameters) use (&$capturedParameters, $mockSession) {
            $capturedParameters = $parameters;

            return $mockSession;
        });

        $client = new Client;
        $client->start();
        $session = $client->createSession([
            'enableManagedSettings' => true,
            'enforceManagedModelDefaults' => true,
            'managedSettings' => ['permissions' => ['deny' => ['Shell(rm *)']]],
            'enableFileChangeTracking' => true,
            'disabledMcpServers' => ['github'],
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);

        expect($session)->toBe($mockSession)
            ->and($capturedParameters['managedSettingsEnabled'] ?? null)->toBeTrue();
    });

    it('createSession sends cloud plugin and directory options in RPC request', function () {
        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::on(fn ($params) => ($params['defaultAgent'] ?? null) === ['excludedTools' => ['builtin:bash']]
                && ($params['workingDirectory'] ?? null) === '/workspace/project'
                && ($params['skillDirectories'] ?? null) === []
                && ($params['agentDirectories'] ?? null) === ['../agents']
                && ($params['hookDirectories'] ?? null) === ['../hooks']
                && ($params['pluginDirectories'] ?? null) === ['/app/plugins/reviewer']
                && ($params['instructionDirectories'] ?? null) === ['../instructions']
                && ($params['enableFileHooks'] ?? null) === false
                && ($params['remoteSession'] ?? null) === 'export'
                && ($params['cloud']['repository'] ?? null) === ['owner' => 'myorg', 'name' => 'myrepo', 'branch' => 'main']
                && ($params['toolSearch'] ?? null) === ['enabled' => true, 'deferThreshold' => 12]
                && ($params['imageGeneration'] ?? null) === ['enabled' => true]
                && ($params['canvasProvider'] ?? null) === ['id' => 'host-canvas']
                && ($params['providers'] ?? null) === [['name' => 'openai', 'baseUrl' => 'https://api.example.com']]
                && ($params['models'] ?? null) === [['id' => 'gpt-custom', 'provider' => 'openai']]
                && ($params['memory'] ?? null) === ['enabled' => true]
                && ($params['featureFlags'] ?? null) === ['feature-a' => true]
                && ($params['expAssignments'] ?? null) === ['assignment' => 'treatment']
                && ($params['refreshCustomInstructions'] ?? null) === false
                && ($params['diagnostics'] ?? null) === ['sources' => ['mcp' => ['level' => 'debug']]]
                && ($params['requestMcpApps'] ?? null) === true
                && ! array_key_exists('enableMcpApps', $params)))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once()->with(null);
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession([
            'defaultAgent' => ['excludedTools' => ['builtin:bash']],
            'workingDirectory' => '/workspace/project',
            'skillDirectories' => [],
            'agentDirectories' => ['../agents'],
            'hookDirectories' => ['../hooks'],
            'pluginDirectories' => ['/app/plugins/reviewer'],
            'instructionDirectories' => ['../instructions'],
            'enableFileHooks' => false,
            'remoteSession' => 'export',
            'toolSearch' => new ToolSearchConfig(enabled: true, deferThreshold: 12),
            'imageGeneration' => new ImageGenerationConfig(enabled: true),
            'canvasProvider' => new CanvasProviderIdentity(id: 'host-canvas'),
            'providers' => [new NamedProviderConfig(name: 'openai', baseUrl: 'https://api.example.com')],
            'models' => [new ProviderModelConfig(id: 'gpt-custom', provider: 'openai')],
            'memory' => new MemoryConfiguration(enabled: true),
            'featureFlags' => ['feature-a' => true],
            'expAssignments' => ['assignment' => 'treatment'],
            'refreshCustomInstructions' => false,
            'diagnostics' => ['sources' => ['mcp' => ['level' => 'debug']]],
            'enableMcpApps' => true,
            'cloud' => [
                'repository' => [
                    'owner' => 'myorg',
                    'name' => 'myrepo',
                    'branch' => 'main',
                ],
            ],
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.resume', Mockery::on(function ($params) {
                $cmds = $params['commands'] ?? null;

                return is_array($cmds)
                    && count($cmds) === 1
                    && $cmds[0] === ['name' => 'rollback', 'description' => 'Rollback last deployment']
                    && ! array_key_exists('handler', $cmds[0])
                    && ($params['askUserVariant'] ?? null) === 'legacy'
                    && ($params['capi'] ?? null) === ['autoTier' => 'efficiency']
                    && ($params['enforceManagedModelDefaults'] ?? null) === true
                    && ! array_key_exists('toolSearch', $params)
                    && ! array_key_exists('providers', $params)
                    && ! array_key_exists('models', $params)
                    && ! array_key_exists('memory', $params)
                    && ! array_key_exists('imageGeneration', $params)
                    && ! array_key_exists('canvasProvider', $params)
                    && ! array_key_exists('featureFlags', $params)
                    && ! array_key_exists('expAssignments', $params)
                    && ! array_key_exists('diagnostics', $params)
                    && ! array_key_exists('enableCitations', $params)
                    && ! array_key_exists('excludedBuiltinAgents', $params)
                    && ! array_key_exists('sessionLimits', $params)
                    && ! array_key_exists('requestMcpApps', $params)
                    && ! array_key_exists('enableMcpApps', $params);
            }))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
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
            'askUserVariant' => 'legacy',
            'capi' => ['autoTier' => 'efficiency'],
            'enforceManagedModelDefaults' => true,
        ]);

        expect($session)->toBe($mockSession);
    });

    it('resumeSession sends plugin and shared config options without cloud in RPC request', function () {
        Event::fake();

        $mockStdioTransport = Mockery::mock(StdioTransport::class);

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdioTransport')->andReturn($mockStdioTransport);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.resume', Mockery::on(fn ($params) => ($params['defaultAgent'] ?? null) === ['excludedTools' => ['builtin:bash']]
                && ($params['workingDirectory'] ?? null) === '/workspace/project'
                && ($params['skillDirectories'] ?? null) === ['/app/skills']
                && ($params['agentDirectories'] ?? null) === ['../agents']
                && ($params['hookDirectories'] ?? null) === ['/app/hooks']
                && ($params['pluginDirectories'] ?? null) === ['/app/plugins/reviewer']
                && ($params['instructionDirectories'] ?? null) === ['/app/instructions']
                && ($params['enableFileHooks'] ?? null) === false
                && ($params['remoteSession'] ?? null) === 'export'
                && ($params['largeOutput'] ?? null) === ['enabled' => true, 'maxSizeBytes' => 1024]
                && ($params['requestMcpApps'] ?? null) === true
                && ! array_key_exists('enableMcpApps', $params)
                && ($params['toolSearch'] ?? null) === ['enabled' => true, 'deferThreshold' => 8]
                && ($params['imageGeneration'] ?? null) === ['enabled' => true]
                && ($params['canvasProvider'] ?? null) === ['id' => 'resume-host']
                && ($params['providers'] ?? null) === [['name' => 'anthropic', 'baseUrl' => 'https://api.anthropic.com']]
                && ($params['models'] ?? null) === [['id' => 'claude-custom', 'provider' => 'anthropic']]
                && ($params['memory'] ?? null) === ['enabled' => true]
                && ($params['featureFlags'] ?? null) === ['resume-feature' => false]
                && ($params['expAssignments'] ?? null) === ['resume-assignment' => 'control']
                && ($params['diagnostics'] ?? null) === ['sources' => ['mcp' => ['level' => 'trace']]]
                && ($params['enableCitations'] ?? null) === false
                && ($params['excludedBuiltinAgents'] ?? null) === ['explainer']
                && ($params['sessionLimits'] ?? null) === ['maxAiCredits' => 20.0]
                && ($params['enableSkills'] ?? null) === true
                && ! array_key_exists('cloud', $params)))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once()->with(null);
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->resumeSession('test-session-123', [
            'defaultAgent' => ['excludedTools' => ['builtin:bash']],
            'workingDirectory' => '/workspace/project',
            'skillDirectories' => ['/app/skills'],
            'agentDirectories' => ['../agents'],
            'hookDirectories' => ['/app/hooks'],
            'pluginDirectories' => ['/app/plugins/reviewer'],
            'instructionDirectories' => ['/app/instructions'],
            'enableFileHooks' => false,
            'remoteSession' => 'export',
            'largeOutput' => ['enabled' => true, 'maxSizeBytes' => 1024],
            'toolSearch' => new ToolSearchConfig(enabled: true, deferThreshold: 8),
            'imageGeneration' => new ImageGenerationConfig(enabled: true),
            'canvasProvider' => new CanvasProviderIdentity(id: 'resume-host'),
            'providers' => [new NamedProviderConfig(name: 'anthropic', baseUrl: 'https://api.anthropic.com')],
            'models' => [new ProviderModelConfig(id: 'claude-custom', provider: 'anthropic')],
            'memory' => new MemoryConfiguration(enabled: true),
            'featureFlags' => ['resume-feature' => false],
            'expAssignments' => ['resume-assignment' => 'control'],
            'diagnostics' => ['sources' => ['mcp' => ['level' => 'trace']]],
            'enableCitations' => false,
            'excludedBuiltinAgents' => ['explainer'],
            'sessionLimits' => new SessionLimitsConfig(maxAiCredits: 20),
            'enableMcpApps' => true,
            'enableSkills' => true,
            'cloud' => [
                'repository' => [
                    'owner' => 'myorg',
                    'name' => 'myrepo',
                    'branch' => 'main',
                ],
            ],
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
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
        $mockRpcClient->shouldReceive('setRequestHandler')->times(12);
        $mockRpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.resume', Mockery::on(fn ($params) => ($params['toolSearch'] ?? null) === ['enabled' => true, 'deferThreshold' => 6]
                && ($params['imageGeneration'] ?? null) === ['enabled' => true]
                && ($params['canvasProvider'] ?? null) === ['id' => 'resume-object-host']
                && ($params['providers'] ?? null) === [['name' => 'provider', 'baseUrl' => 'https://api.example.com']]
                && ($params['models'] ?? null) === [['id' => 'custom-model', 'provider' => 'provider']]
                && ($params['memory'] ?? null) === ['enabled' => true]
                && ($params['featureFlags'] ?? null) === ['object-feature' => true]
                && ($params['expAssignments'] ?? null) === ['assignment' => 'treatment']
                && ($params['diagnostics'] ?? null) === ['sources' => ['mcp' => ['level' => 'debug']]]
                && ($params['enableCitations'] ?? null) === true
                && ($params['excludedBuiltinAgents'] ?? null) === ['explainer']
                && ($params['sessionLimits'] ?? null) === ['maxAiCredits' => 5.0]
                && ! array_key_exists('requestMcpApps', $params)
                && ! array_key_exists('enableMcpApps', $params)))
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);
        $mockRpcClient->shouldReceive('request')
            ->with('session.options.update', [
                'sessionId' => 'test-session-123',
                'skipCustomInstructions' => true,
                'customAgentsLocalOnly' => true,
                'coauthorEnabled' => false,
                'manageScheduleEnabled' => true,
            ])
            ->once()
            ->andReturn(['success' => true]);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('setWorkspacePath')->once()->with(null);
        $mockSession->shouldReceive('registerSkillProvider')->once()->with(null);
        $mockSession->shouldReceive('registerSessionFsProvider')->once()->with(null);
        $mockSession->shouldReceive('registerTools')->once()->with([]);
        $mockSession->shouldReceive('registerCommands')->once()->with([]);
        $mockSession->shouldReceive('setCapabilities')->once();
        $mockSession->shouldReceive('registerPermissionHandler')->once();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->resumeSession('test-session-123', new ResumeSessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            skipCustomInstructions: true,
            customAgentsLocalOnly: true,
            coauthorEnabled: false,
            manageScheduleEnabled: true,
            toolSearch: new ToolSearchConfig(enabled: true, deferThreshold: 6),
            imageGeneration: new ImageGenerationConfig(enabled: true),
            canvasProvider: new CanvasProviderIdentity(id: 'resume-object-host'),
            providers: [new NamedProviderConfig(name: 'provider', baseUrl: 'https://api.example.com')],
            models: [new ProviderModelConfig(id: 'custom-model', provider: 'provider')],
            memory: new MemoryConfiguration(enabled: true),
            featureFlags: ['object-feature' => true],
            expAssignments: ['assignment' => 'treatment'],
            diagnostics: ['sources' => ['mcp' => ['level' => 'debug']]],
            enableCitations: true,
            excludedBuiltinAgents: ['explainer'],
            sessionLimits: new SessionLimitsConfig(maxAiCredits: 5),
        ));

        expect($session)->toBe($mockSession);

        Event::assertDispatched(ResumeSession::class);

        fclose($stdin);
        fclose($stdout);
    });

    it('listModelsUsing returns the client instance', function () {
        $client = new Client;

        expect($client->listModelsUsing(fn () => []))->toBe($client);
    });

    it('listModelsUsing with null clears the handler', function () {
        $client = new Client;
        $client->listModelsUsing(fn () => []);
        $client->listModelsUsing(null);

        expect($client->listModelsUsing(null))->toBe($client);
    });

    it('listModels calls custom handler set via listModelsUsing', function () {
        $client = new Client;
        $client->listModelsUsing(fn () => [
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
        $client->listModelsUsing(fn () => []);

        $models = $client->listModels();

        expect($models)->toBe([]);
    });

    it('getSessionMetadata returns metadata for existing session', function () {
        $mockRpc = Mockery::mock(JsonRpcClient::class);
        $mockRpc->shouldReceive('request')
            ->with('session.getMetadata', ['sessionId' => 'session-123'])
            ->once()
            ->andReturn([
                'session' => [
                    'sessionId' => 'session-123',
                    'startTime' => '2026-01-24T10:00:00Z',
                    'modifiedTime' => '2026-01-24T10:30:00Z',
                    'summary' => 'Test session',
                    'isRemote' => false,
                ],
            ]);

        $client = new Client;
        $ref = new ReflectionClass($client);
        $ref->getProperty('rpcClient')->setValue($client, $mockRpc);
        $ref->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $metadata = $client->getSessionMetadata('session-123');

        expect($metadata)->not->toBeNull()
            ->and($metadata->sessionId)->toBe('session-123')
            ->and($metadata->startTime)->toBe('2026-01-24T10:00:00Z')
            ->and($metadata->modifiedTime)->toBe('2026-01-24T10:30:00Z')
            ->and($metadata->summary)->toBe('Test session')
            ->and($metadata->isRemote)->toBeFalse();
    });

    it('getSessionMetadata returns null for non-existent session', function () {
        $mockRpc = Mockery::mock(JsonRpcClient::class);
        $mockRpc->shouldReceive('request')
            ->with('session.getMetadata', ['sessionId' => 'non-existent'])
            ->once()
            ->andReturn(['session' => null]);

        $client = new Client;
        $ref = new ReflectionClass($client);
        $ref->getProperty('rpcClient')->setValue($client, $mockRpc);
        $ref->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $metadata = $client->getSessionMetadata('non-existent');

        expect($metadata)->toBeNull();
    });

    it('getSessionMetadata returns null when session key is missing', function () {
        $mockRpc = Mockery::mock(JsonRpcClient::class);
        $mockRpc->shouldReceive('request')
            ->with('session.getMetadata', ['sessionId' => 'missing'])
            ->once()
            ->andReturn([]);

        $client = new Client;
        $ref = new ReflectionClass($client);
        $ref->getProperty('rpcClient')->setValue($client, $mockRpc);
        $ref->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $metadata = $client->getSessionMetadata('missing');

        expect($metadata)->toBeNull();
    });

    it('getSessionMetadata throws when not connected', function () {
        $client = new Client;

        expect(fn () => $client->getSessionMetadata('session-123'))
            ->toThrow(RuntimeException::class);
    });
});
