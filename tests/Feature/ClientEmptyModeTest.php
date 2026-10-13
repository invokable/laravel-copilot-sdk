<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\CopilotClientMode;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\BuiltInTools;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Support\ToolSet;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionFsConfig;

function matchesEmptyModePayload(array $payload, array $expected): bool
{
    $actual = array_intersect_key($payload, $expected);
    ksort($actual);
    ksort($expected);

    return $actual === $expected;
}

beforeEach(function () {
    $this->createEmptyModeClient = function (
        ?Closure $requestMatcher = null,
        ?array $postCreatePatch = null,
        ?string $sessionMethod = 'session.create',
        bool $expectSessionRequest = true,
    ): Client {
        $transport = Mockery::mock(StdioTransport::class);
        $processManager = Mockery::mock(ProcessManager::class);
        $processManager->shouldReceive('start')->once();
        $processManager->shouldReceive('getStdioTransport')->andReturn($transport);

        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('start')->once();
        $rpcClient->shouldReceive('setNotificationHandler')->once();
        $rpcClient->shouldReceive('setRequestHandler')->times(26);
        $rpcClient->shouldReceive('request')
            ->with('connect', ['supportedTaskKinds' => ['agent', 'client', 'shell']])
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => Protocol::version()]);

        if ($expectSessionRequest && $sessionMethod !== null) {
            $rpcClient->shouldReceive('request')
                ->with($sessionMethod, Mockery::on($requestMatcher ?? fn () => true))
                ->once()
                ->andReturn(['sessionId' => 'empty-mode-session']);
        }

        if ($postCreatePatch !== null) {
            $rpcClient->shouldReceive('request')
                ->with('session.options.update', $postCreatePatch)
                ->once()
                ->andReturn(['success' => true]);
        }

        $this->app->bind(ProcessManager::class, fn () => $processManager);
        $this->app->bind(JsonRpcClient::class, fn () => $rpcClient);

        if ($expectSessionRequest) {
            $session = Mockery::mock(Session::class);
            $session->shouldReceive('setWorkspacePath')->once()->with(null);
            $session->shouldReceive('registerSkillProvider')->once()->with(null);
            $session->shouldReceive('registerSessionFsProvider')->once()->with(null);
            $session->shouldReceive('registerTools')->once()->with([]);
            $session->shouldReceive('registerCommands')->once()->with([]);
            $session->shouldReceive('setCapabilities')->once()->with(null);
            $session->shouldReceive('registerPermissionHandler')->once();
            $this->app->bind(Session::class, fn () => $session);
        }

        $client = new Client([
            'mode' => CopilotClientMode::Empty,
            'baseDirectory' => '/tmp/copilot-empty-mode',
        ]);
        $client->start();

        return $client;
    };
});

describe('empty client mode', function () {
    it('applies the hardening defaults and isolated tools when creating sessions', function () {
        $expectedPayload = [
            'availableTools' => array_map(
                fn (string $tool) => "builtin:{$tool}",
                BuiltInTools::Isolated,
            ),
            'toolFilterPrecedence' => 'excluded',
            'systemMessage' => [
                'mode' => 'customize',
                'sections' => ['environment_context' => ['action' => 'remove']],
            ],
            'isExperimentalMode' => false,
            'enableSessionTelemetry' => false,
            'mcpOAuthTokenStorage' => 'in-memory',
            'skipEmbeddingRetrieval' => true,
            'embeddingCacheStorage' => 'in-memory',
            'enableOnDemandInstructionDiscovery' => false,
            'enableFileHooks' => false,
            'enableHostGitOperations' => false,
            'enableSessionStore' => false,
            'enableGitHubCacheStore' => false,
            'enableSkills' => false,
            'memory' => ['enabled' => false],
            'customAgentsLocalOnly' => true,
        ];
        $patch = [
            'sessionId' => 'empty-mode-session',
            'skipCustomInstructions' => true,
            'customAgentsLocalOnly' => true,
            'coauthorEnabled' => false,
            'manageScheduleEnabled' => false,
            'installedPlugins' => [],
            'includedBuiltinSkills' => [],
        ];
        $client = ($this->createEmptyModeClient)(
            fn (array $params) => matchesEmptyModePayload($params, $expectedPayload),
            $patch,
        );

        $session = $client->createSession(new SessionConfig(
            availableTools: (new ToolSet)->addBuiltIn(BuiltInTools::Isolated),
            onPermissionRequest: PermissionHandler::approveAll(),
        ));

        expect($session)->toBeInstanceOf(Session::class);
    });

    it('lets explicit session settings override empty mode defaults', function () {
        $expectedPayload = [
            'enableSessionTelemetry' => true,
            'mcpOAuthTokenStorage' => 'persistent',
            'skipEmbeddingRetrieval' => false,
            'embeddingCacheStorage' => 'persistent',
            'enableOnDemandInstructionDiscovery' => true,
            'enableFileHooks' => true,
            'enableHostGitOperations' => true,
            'enableSessionStore' => true,
            'enableGitHubCacheStore' => true,
            'enableSkills' => true,
            'memory' => ['enabled' => true],
            'customAgentsLocalOnly' => false,
            'isExperimentalMode' => true,
            'systemMessage' => [
                'mode' => 'customize',
                'content' => 'Keep this instruction.',
                'sections' => ['environment_context' => ['action' => 'remove']],
            ],
        ];
        $patch = [
            'sessionId' => 'empty-mode-session',
            'skipCustomInstructions' => false,
            'customAgentsLocalOnly' => false,
            'coauthorEnabled' => true,
            'manageScheduleEnabled' => true,
            'installedPlugins' => [],
            'includedBuiltinSkills' => ['review'],
        ];
        $client = ($this->createEmptyModeClient)(
            fn (array $params) => matchesEmptyModePayload($params, $expectedPayload),
            $patch,
        );

        $client->createSession([
            'availableTools' => ['builtin:ask_user'],
            'enableSessionTelemetry' => true,
            'mcpOAuthTokenStorage' => 'persistent',
            'skipEmbeddingRetrieval' => false,
            'embeddingCacheStorage' => 'persistent',
            'enableOnDemandInstructionDiscovery' => true,
            'enableFileHooks' => true,
            'enableHostGitOperations' => true,
            'enableSessionStore' => true,
            'enableGitHubCacheStore' => true,
            'enableSkills' => true,
            'memory' => ['enabled' => true],
            'customAgentsLocalOnly' => false,
            'enableExperimentalMode' => true,
            'systemMessage' => ['mode' => 'append', 'content' => 'Keep this instruction.'],
            'skipCustomInstructions' => false,
            'coauthorEnabled' => true,
            'manageScheduleEnabled' => true,
            'includedBuiltinSkills' => ['review'],
            'onPermissionRequest' => PermissionHandler::approveAll(),
        ]);
    });

    it('applies the same defaults and post-create patch when resuming sessions', function () {
        $expectedPayload = [
            'availableTools' => ['builtin:ask_user'],
            'systemMessage' => [
                'mode' => 'customize',
                'sections' => ['environment_context' => ['action' => 'remove']],
            ],
            'isExperimentalMode' => false,
            'enableSessionTelemetry' => false,
            'mcpOAuthTokenStorage' => 'in-memory',
            'skipEmbeddingRetrieval' => true,
            'embeddingCacheStorage' => 'in-memory',
            'enableOnDemandInstructionDiscovery' => false,
            'enableFileHooks' => false,
            'enableHostGitOperations' => false,
            'enableSessionStore' => false,
            'enableGitHubCacheStore' => false,
            'enableSkills' => false,
            'memory' => ['enabled' => false],
            'customAgentsLocalOnly' => true,
        ];
        $patch = [
            'sessionId' => 'empty-mode-session',
            'skipCustomInstructions' => true,
            'customAgentsLocalOnly' => true,
            'coauthorEnabled' => false,
            'manageScheduleEnabled' => false,
            'installedPlugins' => [],
            'includedBuiltinSkills' => ['approved'],
        ];
        $client = ($this->createEmptyModeClient)(
            fn (array $params) => matchesEmptyModePayload($params, $expectedPayload),
            $patch,
            'session.resume',
        );

        $session = $client->resumeSession(
            'empty-mode-session',
            new ResumeSessionConfig(
                availableTools: (new ToolSet)->addBuiltIn('ask_user'),
                includedBuiltinSkills: ['approved'],
                onPermissionRequest: PermissionHandler::approveAll(),
            ),
        );

        expect($session)->toBeInstanceOf(Session::class);
    });

    it('forwards explicit GitHub cache store configuration when resuming sessions', function () {
        $expectedPayload = [
            'availableTools' => ['builtin:ask_user'],
            'enableGitHubCacheStore' => true,
        ];
        $patch = [
            'sessionId' => 'empty-mode-session',
            'skipCustomInstructions' => true,
            'customAgentsLocalOnly' => true,
            'coauthorEnabled' => false,
            'manageScheduleEnabled' => false,
            'installedPlugins' => [],
            'includedBuiltinSkills' => [],
        ];
        $client = ($this->createEmptyModeClient)(
            fn (array $params) => matchesEmptyModePayload($params, $expectedPayload),
            $patch,
            'session.resume',
        );

        $session = $client->resumeSession(
            'empty-mode-session',
            new ResumeSessionConfig(
                availableTools: (new ToolSet)->addBuiltIn('ask_user'),
                enableGitHubCacheStore: true,
                onPermissionRequest: PermissionHandler::approveAll(),
            ),
        );

        expect($session)->toBeInstanceOf(Session::class);
    });

    it('requires an available tool filter for create and resume', function () {
        $client = ($this->createEmptyModeClient)(sessionMethod: null, expectSessionRequest: false);
        $permissionHandler = PermissionHandler::approveAll();

        expect(fn () => $client->createSession(['onPermissionRequest' => $permissionHandler]))
            ->toThrow(InvalidArgumentException::class, 'availableTools is required');
        expect(fn () => $client->resumeSession('empty-mode-session', ['onPermissionRequest' => $permissionHandler]))
            ->toThrow(InvalidArgumentException::class, 'availableTools is required');
        expect(fn () => $client->createSession([
            'availableTools' => ['*'],
            'onPermissionRequest' => $permissionHandler,
        ]))->toThrow(InvalidArgumentException::class, 'there is no bare wildcard');
        expect(fn () => $client->createSession([
            'availableTools' => 'builtin:ask_user',
            'onPermissionRequest' => $permissionHandler,
        ]))->toThrow(InvalidArgumentException::class, 'not a string');
    });

    it('requires an isolated persistence target unless using an external URL', function () {
        expect(fn () => new Client(['mode' => 'empty']))
            ->toThrow(InvalidArgumentException::class, 'requires a baseDirectory, sessionFs, or an external TCP URL');

        expect(new Client(['mode' => 'empty', 'baseDirectory' => '/isolated/data']))
            ->toBeInstanceOf(Client::class);
        expect(new Client([
            'mode' => 'empty',
            'sessionFs' => new SessionFsConfig('/workspace', '/session-state', 'posix'),
        ]))->toBeInstanceOf(Client::class);
        expect(new Client(['mode' => 'empty', 'cli_url' => 'tcp://127.0.0.1:12345']))
            ->toBeInstanceOf(Client::class);
    });

    it('defaults to copilot-cli mode and rejects unsupported modes', function () {
        $client = new Client(['baseDirectory' => '/isolated/data']);
        $mode = (new ReflectionClass($client))->getProperty('mode')->getValue($client);

        expect($mode)->toBe(CopilotClientMode::CopilotCli);
        expect(fn () => new Client(['mode' => 'unsupported']))
            ->toThrow(InvalidArgumentException::class, 'mode must be either');
    });
});

it('preserves replace messages and removes environment context from other system-message modes', function (
    ?array $systemMessage,
    array $expectedSystemMessage,
) {
    $expectedPatch = [
        'sessionId' => 'empty-mode-session',
        'skipCustomInstructions' => true,
        'customAgentsLocalOnly' => true,
        'coauthorEnabled' => false,
        'manageScheduleEnabled' => false,
        'installedPlugins' => [],
        'includedBuiltinSkills' => [],
    ];
    $client = ($this->createEmptyModeClient)(
        fn (array $params) => ($params['systemMessage'] ?? null) === $expectedSystemMessage,
        $expectedPatch,
    );

    $client->createSession([
        'availableTools' => ['builtin:ask_user'],
        'systemMessage' => $systemMessage,
        'onPermissionRequest' => PermissionHandler::approveAll(),
    ]);
})->with([
    'omitted system message' => [
        null,
        ['mode' => 'customize', 'sections' => ['environment_context' => ['action' => 'remove']]],
    ],
    'append mode' => [
        ['mode' => 'append', 'content' => 'Keep my message.'],
        [
            'mode' => 'customize',
            'content' => 'Keep my message.',
            'sections' => ['environment_context' => ['action' => 'remove']],
        ],
    ],
    'customize mode' => [
        ['mode' => 'customize', 'content' => 'Keep this too.', 'sections' => ['safety' => ['action' => 'remove']]],
        [
            'mode' => 'customize',
            'content' => 'Keep this too.',
            'sections' => [
                'safety' => ['action' => 'remove'],
                'environment_context' => ['action' => 'remove'],
            ],
        ],
    ],
    'customize mode with an explicit environment override' => [
        [
            'mode' => 'customize',
            'sections' => ['environment_context' => ['action' => 'replace', 'content' => 'Explicit']],
        ],
        [
            'mode' => 'customize',
            'sections' => ['environment_context' => ['action' => 'replace', 'content' => 'Explicit']],
        ],
    ],
    'replace mode' => [
        ['mode' => 'replace', 'content' => 'Complete custom prompt.'],
        ['mode' => 'replace', 'content' => 'Complete custom prompt.'],
    ],
]);
