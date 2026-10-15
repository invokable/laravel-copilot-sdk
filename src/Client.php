<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Revolution\Copilot\Concerns\Client\HandlesCanvasProvider;
use Revolution\Copilot\Concerns\Client\HandlesServerRequests;
use Revolution\Copilot\Concerns\Client\HandlesSessionFs;
use Revolution\Copilot\Concerns\Client\HandlesSkillProvider;
use Revolution\Copilot\Concerns\Client\ManagesModels;
use Revolution\Copilot\Concerns\Client\ManagesSessions;
use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Enums\CopilotClientMode;
use Revolution\Copilot\Enums\RuntimeConnectionKind;
use Revolution\Copilot\Enums\TaskKind;
use Revolution\Copilot\Events\Client\ClientStarted;
use Revolution\Copilot\Events\Client\PingPong;
use Revolution\Copilot\Events\Session\CreateSession;
use Revolution\Copilot\Events\Session\ResumeSession;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Support\CanvasHandler;
use Revolution\Copilot\Support\ToolSet;
use Revolution\Copilot\Support\TraceContext;
use Revolution\Copilot\Transport\TcpTransport;
use Revolution\Copilot\Types\CanvasDeclaration;
use Revolution\Copilot\Types\CapiSessionOptions;
use Revolution\Copilot\Types\CommandDefinition;
use Revolution\Copilot\Types\GetAuthStatusResponse;
use Revolution\Copilot\Types\GetStatusResponse;
use Revolution\Copilot\Types\GitHubMcpToolConfig;
use Revolution\Copilot\Types\NamedProviderConfig;
use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveRequest;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveResult;
use Revolution\Copilot\Types\Rpc\GitHubTelemetryNotification;
use Revolution\Copilot\Types\Rpc\InstallationConfirmationRequest;
use Revolution\Copilot\Types\Rpc\ProviderTokenAcquireRequest;
use Revolution\Copilot\Types\Rpc\ProviderTokenAcquireResult;
use Revolution\Copilot\Types\RuntimeConnection;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\SessionFsConfig;
use Revolution\Copilot\Types\TranscriptRecovery;
use RuntimeException;
use Throwable;

/**
 * Main client for interacting with the Copilot CLI.
 */
class Client implements CopilotClient
{
    use Conditionable;
    use HandlesCanvasProvider;
    use HandlesServerRequests;
    use HandlesSessionFs;
    use HandlesSkillProvider;
    use Macroable;
    use ManagesModels;
    use ManagesSessions;

    protected ?ProcessManager $processManager = null;

    protected ?Transport $transport = null;

    protected ?JsonRpcClient $rpcClient = null;

    protected ConnectionState $state = ConnectionState::DISCONNECTED;

    protected ?string $connectionToken = null;

    /**
     * Whether using TCP mode (connecting to existing server).
     */
    protected bool $tcpMode = false;

    /**
     * Active sessions.
     *
     * @var array<string, Session>
     */
    protected array $sessions = [];

    /**
     * Session-scoped GitHub token callbacks keyed by opaque registration ID.
     *
     * @var array<string, array{provider: callable, sessionId: ?string}>
     */
    protected array $gitHubTokenProviders = [];

    /**
     * Session-scoped BYOK bearer token callbacks keyed by provider name.
     *
     * @var array<string, array<string, callable(array{providerName: string, sessionId: string}): string>>
     */
    protected array $bearerTokenProviders = [];

    /**
     * Validated `sessionFs` client option, registered with the runtime on start().
     */
    protected ?SessionFsConfig $sessionFsConfig = null;

    protected CopilotClientMode $mode = CopilotClientMode::CopilotCli;

    /**
     * @var list<string>
     */
    protected array $builtinPluginDirectories = [];

    /**
     * @var Closure(GitHubTelemetryNotification): mixed|null
     */
    protected ?Closure $onGitHubTelemetry = null;

    /**
     * @var Closure(ExtensionLaunchProviderResolveRequest): ExtensionLaunchProviderResolveResult|array|null
     */
    protected ?Closure $extensionLaunchProvider = null;

    /**
     * Create a new CopilotClient.
     */
    public function __construct(protected array $options = [])
    {
        $telemetryOption = array_key_exists('onGitHubTelemetry', $options)
            ? 'onGitHubTelemetry'
            : (array_key_exists('on_github_telemetry', $options) ? 'on_github_telemetry' : null);
        $onGitHubTelemetry = $telemetryOption === null ? null : $options[$telemetryOption];
        if ($telemetryOption !== null && ! is_callable($onGitHubTelemetry)) {
            throw new InvalidArgumentException('onGitHubTelemetry must be callable.');
        }

        $this->onGitHubTelemetry = $onGitHubTelemetry === null
            ? null
            : Closure::fromCallable($onGitHubTelemetry);
        $extensionLaunchProvider = array_key_exists('extensionLaunchProvider', $options)
            ? $options['extensionLaunchProvider']
            : ($options['extension_launch_provider'] ?? null);
        if ($extensionLaunchProvider !== null && ! is_callable($extensionLaunchProvider)) {
            throw new InvalidArgumentException('extensionLaunchProvider must be callable.');
        }
        $this->extensionLaunchProvider = $extensionLaunchProvider === null
            ? null
            : Closure::fromCallable($extensionLaunchProvider);
        $configuredMode = $options['mode'] ?? CopilotClientMode::CopilotCli;
        $this->mode = $configuredMode instanceof CopilotClientMode
            ? $configuredMode
            : (is_string($configuredMode) ? CopilotClientMode::tryFrom($configuredMode) : null)
                ?? throw new InvalidArgumentException('mode must be either "copilot-cli" or "empty".');
        $this->builtinPluginDirectories = $this->normalizeBuiltinPluginDirectories($options);
        $this->sessionFsConfig = $this->normalizeSessionFsConfig($options);

        $connection = $this->normalizeConnection($options);
        $baseDirectory = $options['baseDirectory'] ?? $options['base_directory'] ?? $options['copilot_home'] ?? null;

        if ($this->mode === CopilotClientMode::Empty
            && ! filled($baseDirectory)
            && $this->sessionFsConfig === null
            && $connection->kindValue() !== RuntimeConnectionKind::URI->value) {
            throw new InvalidArgumentException(
                'mode "empty" requires a baseDirectory, sessionFs, or an external TCP URL.',
            );
        }

        // URI mode: connect to existing server
        if ($connection->kindValue() === RuntimeConnectionKind::URI->value) {
            // Validate auth options with external server
            if (filled(data_get($options, 'github_token')) || data_get($options, 'use_logged_in_user') !== null) {
                throw new InvalidArgumentException(
                    'github_token and use_logged_in_user cannot be used with RuntimeConnection::forUri() (external server manages its own auth)',
                );
            }

            $this->tcpMode = true;
            $this->connectionToken = $connection->connectionToken;
            $this->transport = TcpTransport::fromUrl($connection->url ?? '');

            return;
        }

        if ($connection->kindValue() === RuntimeConnectionKind::TCP->value) {
            throw new InvalidArgumentException(
                'SDK-spawned TCP runtime connections are not supported yet. Use RuntimeConnection::forUri() or cli_url to connect to an existing runtime.',
            );
        }

        // Stdio mode: start CLI process
        $this->processManager = app(ProcessManager::class, [
            'cliPath' => $connection->path ?? data_get($options, 'cli_path'),
            'cliArgs' => $connection->args ?? data_get($options, 'cli_args', []),
            'cwd' => data_get($options, 'cwd'),
            'logLevel' => data_get($options, 'log_level', 'info'),
            'env' => data_get($options, 'env'),
            'gitHubToken' => data_get($options, 'github_token'),
            'useLoggedInUser' => data_get($options, 'use_logged_in_user'),
            'telemetry' => data_get($options, 'telemetry'),
            'sessionIdleTimeoutSeconds' => data_get($options, 'session_idle_timeout_seconds', 0),
            'baseDirectory' => $baseDirectory,
            'remote' => data_get($options, 'remote', false),
            'mode' => $this->mode,
        ]);
    }

    /**
     * @return list<string>
     */
    protected function normalizeBuiltinPluginDirectories(array $options): array
    {
        $directories = array_key_exists('builtinPluginDirectories', $options)
            ? $options['builtinPluginDirectories']
            : ($options['builtin_plugin_directories'] ?? []);

        if (! is_array($directories) || ! array_is_list($directories)) {
            throw new InvalidArgumentException('builtinPluginDirectories must be a list of absolute paths.');
        }

        foreach ($directories as $directory) {
            if (! is_string($directory) || ! $this->isAbsolutePath($directory)) {
                throw new InvalidArgumentException(
                    'builtinPluginDirectories must contain only absolute paths: '.
                    (is_string($directory) ? $directory : get_debug_type($directory)),
                );
            }
        }

        return $directories;
    }

    protected function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || (
            strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && in_array($path[2], ['\\', '/'], true)
        );
    }

    protected function normalizeConnection(array $options): RuntimeConnection
    {
        $connection = data_get($options, 'connection');

        if ($connection instanceof RuntimeConnection) {
            return $connection;
        }

        if ($connection instanceof Arrayable) {
            return RuntimeConnection::fromArray($connection->toArray());
        }

        if (is_array($connection)) {
            return RuntimeConnection::fromArray($connection);
        }

        if (filled(data_get($options, 'cli_url'))) {
            return RuntimeConnection::forUri(data_get($options, 'cli_url'));
        }

        return RuntimeConnection::forStdio(
            path: data_get($options, 'cli_path'),
            args: data_get($options, 'cli_args', []),
        );
    }

    /**
     * Start the CLI server and establish connection.
     *
     * @throws Throwable
     */
    public function start(): static
    {
        if ($this->state === ConnectionState::CONNECTED) {
            return $this;
        }

        $this->state = ConnectionState::CONNECTING;

        try {
            if ($this->tcpMode) {
                // TCP mode: connect to existing server
                $this->transport->start();
            } else {
                // Stdio mode: start the CLI process
                $this->processManager->start();
                $this->transport = $this->processManager->getStdioTransport();
            }

            // Create JSON-RPC client
            $this->rpcClient = app(JsonRpcClient::class, [
                'transport' => $this->transport,
            ]);

            $this->rpcClient->start();

            // Set up notification handler for session events
            $this->rpcClient->setNotificationHandler(
                fn (string $method, array $params) => $this->handleNotification($method, $params),
            );

            // Set up request handlers for tool calls and permission requests
            $this->rpcClient->setRequestHandler(
                'tool.call',
                fn (array $params) => $this->handleToolCall($params),
            );

            $this->rpcClient->setRequestHandler(
                'permission.request',
                fn (array $params) => $this->handlePermissionRequest($params),
            );

            $this->rpcClient->setRequestHandler(
                'userInput.request',
                fn (array $params) => $this->handleUserInputRequest($params),
            );

            $this->rpcClient->setRequestHandler(
                'hooks.invoke',
                fn (array $params) => $this->handleHooksInvoke($params),
            );

            $this->rpcClient->setRequestHandler(
                'gitHubToken.getToken',
                fn (array $params) => $this->acquireGitHubToken($params),
            );

            $this->rpcClient->setRequestHandler(
                'providerToken.getToken',
                fn (array $params) => $this->acquireProviderToken($params),
            );

            $this->rpcClient->setRequestHandler(
                'extensionLaunchProvider.resolve',
                fn (array $params) => $this->handleExtensionLaunchProviderResolve($params),
            );

            foreach ([
                'canvas.open' => 'handleCanvasOpen',
                'canvas.close' => 'handleCanvasClose',
                'canvas.action.invoke' => 'handleCanvasActionInvoke',
            ] as $method => $handler) {
                $this->rpcClient->setRequestHandler(
                    $method,
                    fn (array $params) => $this->{$handler}($params),
                );
            }

            $this->rpcClient->setRequestHandler(
                'skillProvider.list',
                fn (array $params, CancellationToken $token) => $this->handleSkillProviderList($params, $token),
            );

            $this->rpcClient->setRequestHandler(
                'skillProvider.read',
                fn (array $params, CancellationToken $token) => $this->handleSkillProviderRead($params, $token),
            );

            foreach ([
                'sessionFs.readFile' => 'handleSessionFsReadFile',
                'sessionFs.readFileBytes' => 'handleSessionFsReadFileBytes',
                'sessionFs.writeFile' => 'handleSessionFsWriteFile',
                'sessionFs.writeFileBytes' => 'handleSessionFsWriteFileBytes',
                'sessionFs.appendFile' => 'handleSessionFsAppendFile',
                'sessionFs.exists' => 'handleSessionFsExists',
                'sessionFs.stat' => 'handleSessionFsStat',
                'sessionFs.mkdir' => 'handleSessionFsMkdir',
                'sessionFs.readdir' => 'handleSessionFsReaddir',
                'sessionFs.readdirWithTypes' => 'handleSessionFsReaddirWithTypes',
                'sessionFs.rm' => 'handleSessionFsRm',
                'sessionFs.rename' => 'handleSessionFsRename',
                'sessionFs.sqliteQuery' => 'handleSessionFsSqliteQuery',
                'sessionFs.sqliteTransaction' => 'handleSessionFsSqliteTransaction',
                'sessionFs.sqliteExists' => 'handleSessionFsSqliteExists',
            ] as $method => $handler) {
                $this->rpcClient->setRequestHandler(
                    $method,
                    fn (array $params) => $this->{$handler}($params),
                );
            }

            if (is_callable($this->options['installation_confirmation_handler']
                ?? $this->options['installationConfirmationHandler'] ?? null)) {
                $this->rpcClient->setRequestHandler(
                    'installations.confirm',
                    fn (array $params) => $this->handleInstallationConfirmation($params),
                );
            }

            $this->state = ConnectionState::CONNECTED;

            // Verify protocol version
            $this->verifyProtocolVersion();

            if ($this->extensionLaunchProvider !== null) {
                $this->rpc()->registerExtensionLaunchProvider();
            }

            if ($this->builtinPluginDirectories !== []) {
                try {
                    $this->rpc()->plugins()->builtin()->set([
                        'paths' => $this->builtinPluginDirectories,
                    ]);
                } catch (Throwable $e) {
                    try {
                        $this->stop();
                    } catch (Throwable) {
                        try {
                            if ($this->tcpMode) {
                                $this->transport?->stop();
                            } else {
                                $this->processManager?->stop();
                            }
                        } catch (Throwable) {
                        }
                    }

                    throw $e;
                }
            }

            $this->registerSessionFsWithRuntime();

            ClientStarted::dispatch($this);

            return $this;
        } catch (Throwable $e) {
            $this->state = ConnectionState::ERROR;
            throw $e;
        }
    }

    /**
     * Resolve an installation review through the host's explicit confirmation
     * callback. The challenge and fingerprint are always echoed unchanged.
     */
    protected function handleInstallationConfirmation(array $params): array
    {
        $request = InstallationConfirmationRequest::fromArray($params);
        $handler = $this->options['installation_confirmation_handler']
            ?? $this->options['installationConfirmationHandler'];
        $decision = $handler($request);
        $decision = $decision instanceof \BackedEnum ? $decision->value : $decision;

        return $request->decision($decision)->toArray();
    }

    /**
     * Stop the CLI server and close all sessions.
     *
     * @return array<Throwable> Errors encountered during cleanup
     */
    public function stop(): array
    {
        $errors = [];

        // Destroy all sessions
        foreach ($this->sessions as $session) {
            try {
                $session->disconnect();
            } catch (Throwable $e) {
                $errors[] = $e;
            }
        }

        $this->sessions = [];
        $this->gitHubTokenProviders = [];
        $this->bearerTokenProviders = [];

        if (! $this->tcpMode && $this->state === ConnectionState::CONNECTED && $this->rpcClient !== null) {
            try {
                $this->rpc()->runtime()->shutdown();
            } catch (Throwable $e) {
                $errors[] = new RuntimeException(
                    'Failed to gracefully shut down runtime: '.$e->getMessage(),
                    previous: $e,
                );
            }
        }

        // Stop JSON-RPC client
        if ($this->rpcClient !== null) {
            $this->rpcClient->stop();
            $this->rpcClient = null;
        }

        // Stop transport/process
        if ($this->tcpMode) {
            $this->transport?->stop();
        } else {
            $this->processManager?->stop();
        }

        $this->state = ConnectionState::DISCONNECTED;

        return $errors;
    }

    /**
     * Typed server-scoped RPC methods.
     */
    public function rpc(): ServerRpc
    {
        $this->ensureConnected();

        return new ServerRpc($this->rpcClient);
    }

    /**
     * Check if the client is using TCP mode.
     */
    public function isTcpMode(): bool
    {
        return $this->tcpMode;
    }

    /**
     * Create a new conversation session.
     *
     * Custom-agent declarative hooks are forwarded with their agent definition; top-level hooks are session callbacks.
     *
     * @param  SessionConfig|array{session_id?: string, name?: string, model?: string, tools?: array, system_message?: array, available_tools?: array, provider?: array, on_permission_request: callable, on_user_input_request?: callable, on_mcp_auth_request?: callable, hooks?: array, working_directory?: string, streaming?: bool, mcp_servers?: array, custom_agents?: array, config_dir?: string, skill_directories?: array, disabled_skills?: array, interactiveUser?: bool}  $config
     *
     * @throws JsonRpcException
     */
    public function createSession(SessionConfig|array $config): CopilotSession
    {
        $this->ensureConnected();

        $config = $this->normalizeSessionConfigAliases(is_array($config)
            ? $config
            : [
                ...$config->toArray(),
                'onMcpAuthRequest' => $config->onMcpAuthRequest,
                'provider' => $config->provider,
                'providers' => $config->providers,
                'gitHubTokenProvider' => $config->gitHubTokenProvider,
                'skillProvider' => $config->skillProvider,
                'sessionFsProvider' => $config->sessionFsProvider,
            ]);
        $config = $this->normalizeToolFilters($config);
        $config = $this->applyModeDefaults($config);
        [$config, $bearerTokenProviders] = $this->extractBearerTokenProviders($config);

        if (isset($config['gitHubToken'], $config['gitHubTokenProvider'])) {
            throw new InvalidArgumentException('gitHubToken and gitHubTokenProvider are mutually exclusive');
        }

        if (! isset($config['onPermissionRequest']) || ! is_callable($config['onPermissionRequest'])) {
            throw new InvalidArgumentException(
                'An onPermissionRequest handler is required when creating a session. '
                .'For example, to allow all permissions, use new SessionConfig(onPermissionRequest: PermissionHandler::approveAll()).',
            );
        }

        $canvasHandler = $config['canvasHandler'] ?? null;
        $this->validateCanvasHandler($canvasHandler);
        $config['canvases'] = $this->normalizeCanvasDeclarations($config['canvases'] ?? null);

        $skillProvider = $config['skillProvider'] ?? null;
        $this->validateSkillProvider($skillProvider);
        $sessionFsProvider = $config['sessionFsProvider'] ?? null;
        $this->validateSessionFsProvider($sessionFsProvider);

        $isCloudSession = ($config['cloud'] ?? null) !== null;
        if ($isCloudSession && $skillProvider !== null) {
            throw new InvalidArgumentException('Skill providers are not supported for cloud sessions.');
        }

        // Cloud sessions run remotely and have no local session filesystem to delegate.
        $sessionFsFactory = $isCloudSession ? null : $this->resolveSessionFsFactory($config);

        $tokenProviderRegistrationId = $this->registerGitHubTokenProvider(
            $config['gitHubTokenProvider'] ?? null,
        );

        $tools = $config['tools'] ?? [];
        $toolsForRequest = array_map(fn ($tool) => array_filter([
            'name' => $tool['name'],
            'description' => $tool['description'] ?? null,
            'parameters' => $tool['parameters'] ?? null,
            'overridesBuiltInTool' => $tool['overridesBuiltInTool'] ?? null,
            'skipPermission' => $tool['skipPermission'] ?? null,
            'defer' => $tool['defer'] ?? null,
            'metadata' => $tool['metadata'] ?? null,
            'isTerminal' => $tool['isTerminal'] ?? null,
        ], fn ($v) => $v !== null), $tools);

        $commands = $config['commands'] ?? [];
        $commandsForRequest = $this->normalizeCommandsForRequest($commands);
        $hooks = $config['hooks'] ?? null;
        $hasHooks = $hooks !== null && ! empty(array_filter(
            is_array($hooks) ? $hooks : $hooks->toArray(),
        ));

        // Local sessions use a client-generated ID so incoming session callbacks
        // can be routed while the runtime is still processing session.create.
        $preRegisteredSessionId = ! $isCloudSession && (
            $skillProvider !== null
            || $sessionFsProvider !== null
            || $sessionFsFactory !== null
            || $bearerTokenProviders !== []
            || $canvasHandler !== null
            || $config['canvases'] !== null
        )
            ? ($config['sessionId'] ?? (string) Str::uuid())
            : null;
        $session = null;
        if ($preRegisteredSessionId !== null) {
            $session = app(Session::class, [
                'sessionId' => $preRegisteredSessionId,
                'client' => $this->rpcClient,
                'managedSettingsEnabled' => ($config['enableManagedSettings'] ?? false) === true
                    || array_key_exists('managedSettings', $config),
            ]);
            if ($sessionFsFactory !== null) {
                try {
                    $sessionFsProvider = $this->createSessionFsProviderFor($session, $sessionFsFactory);
                } catch (Throwable $e) {
                    if ($tokenProviderRegistrationId !== null) {
                        unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
                    }
                    throw $e;
                }
            }
            $session->registerSkillProvider($skillProvider);
            $session->registerSessionFsProvider($sessionFsProvider);
            $this->registerSessionHandlers($session, $tools, $commands, $config, $hooks);
            $this->sessions[$preRegisteredSessionId] = $session;
            if ($bearerTokenProviders !== []) {
                $this->bearerTokenProviders[$preRegisteredSessionId] = $bearerTokenProviders;
            }
        }

        try {
            $response = $this->rpcClient->request('session.create', array_filter([
                ...TraceContext::get(),
                'sessionId' => $preRegisteredSessionId ?? ($config['sessionId'] ?? null),
                'name' => $config['name'] ?? null,
                'allowedModels' => $config['allowedModels'] ?? null,
                'clientName' => $config['clientName'] ?? null,
                'interactiveUser' => $config['interactiveUser'] ?? null,
                'model' => $config['model'] ?? null,
                'reasoningEffort' => $config['reasoningEffort'] ?? null,
                'reasoningSummary' => $config['reasoningSummary'] ?? null,
                'isExperimentalMode' => $config['enableExperimentalMode'] ?? null,
                'contextTier' => $config['contextTier'] ?? null,
                'modelCapabilities' => $config['modelCapabilities'] ?? null,
                'tools' => $toolsForRequest ?: null,
                'commands' => $commandsForRequest,
                'systemMessage' => $config['systemMessage'] ?? null,
                'availableTools' => $config['availableTools'] ?? null,
                'excludedTools' => $config['excludedTools'] ?? null,
                'toolFilterPrecedence' => 'excluded',
                'provider' => $config['provider'] ?? null,
                'toolSearch' => $this->normalizeArrayableValue($config['toolSearch'] ?? null),
                'providers' => $this->normalizeArrayableValue($config['providers'] ?? null),
                'models' => $this->normalizeArrayableValue($config['models'] ?? null),
                'memory' => $this->normalizeArrayableValue($config['memory'] ?? null),
                'imageGeneration' => $this->normalizeArrayableValue($config['imageGeneration'] ?? null),
                'canvasProvider' => $this->normalizeArrayableValue($config['canvasProvider'] ?? null),
                'featureFlags' => $config['featureFlags'] ?? null,
                'expAssignments' => $this->normalizeArrayableValue($config['expAssignments'] ?? null),
                'refreshCustomInstructions' => $config['refreshCustomInstructions'] ?? null,
                'diagnostics' => $config['diagnostics'] ?? null,
                'capi' => $this->normalizeCapiSessionOptions($config['capi'] ?? null),
                'requestPermission' => isset($config['onPermissionRequest']),
                'requestUserInput' => isset($config['onUserInputRequest']),
                'requestElicitation' => isset($config['onElicitationRequest']),
                'askUserVariant' => ($config['askUserVariant'] ?? null) instanceof \BackedEnum
                    ? $config['askUserVariant']->value
                    : ($config['askUserVariant'] ?? null),
                'requestExitPlanMode' => isset($config['onExitPlanModeRequest']),
                'requestAutoModeSwitch' => isset($config['onAutoModeSwitchRequest']),
                'hooks' => $hasHooks,
                'workingDirectory' => $config['workingDirectory'] ?? null,
                'additionalDirectories' => $config['additionalDirectories'] ?? null,
                'streaming' => $config['streaming'] ?? null,
                'includeSubAgentStreamingEvents' => $config['includeSubAgentStreamingEvents'] ?? null,
                'mcpServers' => $config['mcpServers'] ?? null,
                'envValueMode' => 'direct',
                'customAgents' => $config['customAgents'] ?? null,
                'customAgentsLocalOnly' => $config['customAgentsLocalOnly'] ?? null,
                'defaultAgent' => $config['defaultAgent'] ?? null,
                'agent' => $config['agent'] ?? null,
                'configDir' => $config['configDir'] ?? null,
                'configDirectory' => $config['configDirectory'] ?? null,
                'enableConfigDiscovery' => $config['enableConfigDiscovery'] ?? null,
                'skillDirectories' => $config['skillDirectories'] ?? null,
                'agentDirectories' => $config['agentDirectories'] ?? null,
                'hookDirectories' => $config['hookDirectories'] ?? null,
                'pluginDirectories' => $config['pluginDirectories'] ?? null,
                'instructionDirectories' => $config['instructionDirectories'] ?? null,
                'disabledSkills' => $config['disabledSkills'] ?? null,
                'infiniteSessions' => $config['infiniteSessions'] ?? null,
                'enableSessionTelemetry' => $config['enableSessionTelemetry'] ?? null,
                'enableGitHubTelemetryForwarding' => $this->onGitHubTelemetry !== null
                    ? true
                    : ($config['enableGitHubTelemetryForwarding'] ?? null),
                'gitHubToken' => $config['gitHubToken'] ?? null,
                'gitHubTokenProviderRegistrationId' => $tokenProviderRegistrationId,
                'remoteSession' => $config['remoteSession'] ?? null,
                'cloud' => $config['cloud'] ?? null,
                'canvases' => $config['canvases'] ?? null,
                'requestCanvasRenderer' => $config['requestCanvasRenderer'] ?? null,
                'requestExtensions' => $config['requestExtensions'] ?? null,
                'extensionInfo' => $config['extensionInfo'] ?? null,
                'largeOutput' => $config['largeOutput'] ?? null,
                'extensionSdkPath' => $config['extensionSdkPath'] ?? null,
                'requestMcpApps' => ($config['enableMcpApps'] ?? null) === true ? true : null,
                'githubMcpToolConfig' => $this->normalizeGitHubMcpToolConfig($config['githubMcpToolConfig'] ?? null),
                'mcpOAuthTokenStorage' => $config['mcpOAuthTokenStorage'] ?? null,
                'authClientIdMetadataUrl' => $config['authClientIdMetadataUrl'] ?? null,
                'skipEmbeddingRetrieval' => $config['skipEmbeddingRetrieval'] ?? null,
                'embeddingCacheStorage' => $config['embeddingCacheStorage'] ?? null,
                'organizationCustomInstructions' => $config['organizationCustomInstructions'] ?? null,
                'enableOnDemandInstructionDiscovery' => $config['enableOnDemandInstructionDiscovery'] ?? null,
                'enableFileHooks' => $config['enableFileHooks'] ?? null,
                'enableHostGitOperations' => $config['enableHostGitOperations'] ?? null,
                'enableSessionStore' => $config['enableSessionStore'] ?? null,
                'enableGitHubCacheStore' => $config['enableGitHubCacheStore'] ?? null,
                'enableSkills' => $config['enableSkills'] ?? null,
                'enableCitations' => $config['enableCitations'] ?? null,
                'excludedBuiltinAgents' => $config['excludedBuiltinAgents'] ?? null,
                'includedBuiltinAgents' => $config['includedBuiltinAgents'] ?? null,
                'sessionLimits' => $this->normalizeArrayableValue($config['sessionLimits'] ?? null),
                'enableManagedSettings' => $config['enableManagedSettings'] ?? null,
                'enforceManagedModelDefaults' => $config['enforceManagedModelDefaults'] ?? null,
                'managedSettings' => $config['managedSettings'] ?? null,
                'enableFileChangeTracking' => $config['enableFileChangeTracking'] ?? null,
                'disabledMcpServers' => $config['disabledMcpServers'] ?? null,
                'includedBuiltinSkills' => $config['includedBuiltinSkills'] ?? null,
                'hasSkillProvider' => $skillProvider !== null ? true : null,
                'hasSessionFsProvider' => $sessionFsProvider !== null ? true : null,
            ], fn ($v) => $v !== null));
        } catch (Throwable $e) {
            if ($tokenProviderRegistrationId !== null) {
                unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
            }
            if ($preRegisteredSessionId !== null) {
                unset($this->sessions[$preRegisteredSessionId]);
                unset($this->bearerTokenProviders[$preRegisteredSessionId]);
            }
            throw $e;
        }

        $sessionId = $response['sessionId'] ?? null;
        if (! is_string($sessionId) || $sessionId === '') {
            if ($preRegisteredSessionId !== null) {
                unset($this->sessions[$preRegisteredSessionId]);
                unset($this->bearerTokenProviders[$preRegisteredSessionId]);
            }
            if ($tokenProviderRegistrationId !== null) {
                unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
            }

            throw new RuntimeException('Failed to create session');
        }
        if ($preRegisteredSessionId !== null && $sessionId !== $preRegisteredSessionId) {
            unset($this->sessions[$preRegisteredSessionId]);
            unset($this->bearerTokenProviders[$preRegisteredSessionId]);
            if ($tokenProviderRegistrationId !== null) {
                unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
            }

            throw new RuntimeException('Runtime returned a different session ID than requested');
        }
        if ($tokenProviderRegistrationId !== null) {
            $this->gitHubTokenProviders[$tokenProviderRegistrationId]['sessionId'] = $sessionId;
        }
        $workspacePath = $response['workspacePath'] ?? null;
        $capabilities = $response['capabilities'] ?? null;

        $session ??= app(Session::class, [
            'sessionId' => $sessionId,
            'client' => $this->rpcClient,
            'workspacePath' => $workspacePath,
            'managedSettingsEnabled' => ($config['enableManagedSettings'] ?? false) === true
                || array_key_exists('managedSettings', $config),
        ]);
        if ($preRegisteredSessionId === null) {
            $this->registerSessionHandlers($session, $tools, $commands, $config, $hooks);
        }
        $session->setWorkspacePath($workspacePath);
        if ($tokenProviderRegistrationId !== null || $bearerTokenProviders !== []) {
            $session->setOnDisconnected(function () use ($tokenProviderRegistrationId, $sessionId): void {
                if ($tokenProviderRegistrationId !== null) {
                    unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
                }
                unset($this->bearerTokenProviders[$sessionId]);
            });
        }
        $session->setCapabilities($capabilities);
        $session->registerSkillProvider($skillProvider);
        $session->registerSessionFsProvider($sessionFsProvider);
        if ($preRegisteredSessionId === null && $bearerTokenProviders !== []) {
            $this->bearerTokenProviders[$sessionId] = $bearerTokenProviders;
        }
        $this->sessions[$sessionId] = $session;

        try {
            $this->registerMcpAuthRequestInterest($sessionId, $config);
            $this->applyPostCreateOptionsPatch($sessionId, $session, $config);
        } catch (Throwable $e) {
            unset($this->sessions[$sessionId]);
            unset($this->bearerTokenProviders[$sessionId]);
            if ($tokenProviderRegistrationId !== null) {
                unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
            }
            throw $e;
        }

        CreateSession::dispatch($session);

        return $session;
    }

    /**
     * Resume an existing session.
     *
     * @param  ResumeSessionConfig|array{tools?: array, provider?: array, on_permission_request: callable, on_user_input_request?: callable, on_mcp_auth_request?: callable, hooks?: array, working_directory?: string, streaming?: bool, mcp_servers?: array, custom_agents?: array, skill_directories?: array, disabled_skills?: array, interactiveUser?: bool}  $config
     *
     * @throws JsonRpcException
     */
    public function resumeSession(string $sessionId, ResumeSessionConfig|array $config): CopilotSession
    {
        $this->ensureConnected();

        $config = $this->normalizeSessionConfigAliases(is_array($config)
            ? $config
            : [
                ...$config->toArray(),
                'onMcpAuthRequest' => $config->onMcpAuthRequest,
                'provider' => $config->provider,
                'gitHubTokenProvider' => $config->gitHubTokenProvider,
                'skillProvider' => $config->skillProvider,
                'sessionFsProvider' => $config->sessionFsProvider,
            ]);
        $config = $this->normalizeToolFilters($config);
        $config = $this->applyModeDefaults($config);
        [$config, $bearerTokenProviders] = $this->extractBearerTokenProviders($config);

        if (isset($config['gitHubToken'], $config['gitHubTokenProvider'])) {
            throw new InvalidArgumentException('gitHubToken and gitHubTokenProvider are mutually exclusive');
        }

        if (! isset($config['onPermissionRequest']) || ! is_callable($config['onPermissionRequest'])) {
            throw new InvalidArgumentException(
                'An onPermissionRequest handler is required when resuming a session. '
                .'For example, to allow all permissions, use new ResumeSessionConfig(onPermissionRequest: PermissionHandler::approveAll()).',
            );
        }

        $canvasHandler = $config['canvasHandler'] ?? null;
        $this->validateCanvasHandler($canvasHandler);
        $config['canvases'] = $this->normalizeCanvasDeclarations($config['canvases'] ?? null);

        $skillProvider = $config['skillProvider'] ?? null;
        $this->validateSkillProvider($skillProvider);
        $sessionFsProvider = $config['sessionFsProvider'] ?? null;
        $this->validateSessionFsProvider($sessionFsProvider);
        $sessionFsFactory = $this->resolveSessionFsFactory($config);

        $tokenProviderRegistrationId = $this->registerGitHubTokenProvider(
            $config['gitHubTokenProvider'] ?? null,
            $sessionId,
        );

        $tools = $config['tools'] ?? [];
        $toolsForRequest = array_map(fn ($tool) => array_filter([
            'name' => $tool['name'],
            'description' => $tool['description'] ?? null,
            'parameters' => $tool['parameters'] ?? null,
            'overridesBuiltInTool' => $tool['overridesBuiltInTool'] ?? null,
            'skipPermission' => $tool['skipPermission'] ?? null,
            'defer' => $tool['defer'] ?? null,
            'metadata' => $tool['metadata'] ?? null,
            'isTerminal' => $tool['isTerminal'] ?? null,
        ], fn ($v) => $v !== null), $tools);

        $commands = $config['commands'] ?? [];
        $commandsForRequest = $this->normalizeCommandsForRequest($commands);

        $hooks = $config['hooks'] ?? null;
        $hasHooks = $hooks !== null && ! empty(array_filter(
            is_array($hooks) ? $hooks : $hooks->toArray(),
        ));

        // Register callback handlers before resume: the runtime can issue
        // session-scoped requests while replaying persisted workspace state.
        $session = app(Session::class, [
            'sessionId' => $sessionId,
            'client' => $this->rpcClient,
            'managedSettingsEnabled' => ($config['enableManagedSettings'] ?? false) === true
                || array_key_exists('managedSettings', $config),
        ]);
        if ($sessionFsFactory !== null) {
            try {
                $sessionFsProvider = $this->createSessionFsProviderFor($session, $sessionFsFactory);
            } catch (Throwable $e) {
                if ($tokenProviderRegistrationId !== null) {
                    unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
                }
                throw $e;
            }
        }
        $session->registerSkillProvider($skillProvider);
        $session->registerSessionFsProvider($sessionFsProvider);
        $this->registerSessionHandlers($session, $tools, $commands, $config, $hooks);
        $this->sessions[$sessionId] = $session;
        unset($this->bearerTokenProviders[$sessionId]);
        if ($bearerTokenProviders !== []) {
            $this->bearerTokenProviders[$sessionId] = $bearerTokenProviders;
        }

        try {
            $response = $this->rpcClient->request('session.resume', array_filter([
                ...TraceContext::get(),
                'sessionId' => $sessionId,
                'allowedModels' => $config['allowedModels'] ?? null,
                'clientName' => $config['clientName'] ?? null,
                'interactiveUser' => $config['interactiveUser'] ?? null,
                'model' => $config['model'] ?? null,
                'reasoningEffort' => $config['reasoningEffort'] ?? null,
                'reasoningSummary' => $config['reasoningSummary'] ?? null,
                'isExperimentalMode' => $config['enableExperimentalMode'] ?? null,
                'contextTier' => $config['contextTier'] ?? null,
                'modelCapabilities' => $config['modelCapabilities'] ?? null,
                'tools' => $toolsForRequest ?: null,
                'commands' => $commandsForRequest,
                'systemMessage' => $config['systemMessage'] ?? null,
                'availableTools' => $config['availableTools'] ?? null,
                'excludedTools' => $config['excludedTools'] ?? null,
                'toolFilterPrecedence' => 'excluded',
                'provider' => $config['provider'] ?? null,
                'toolSearch' => $this->normalizeArrayableValue($config['toolSearch'] ?? null),
                'providers' => $this->normalizeArrayableValue($config['providers'] ?? null),
                'models' => $this->normalizeArrayableValue($config['models'] ?? null),
                'memory' => $this->normalizeArrayableValue($config['memory'] ?? null),
                'imageGeneration' => $this->normalizeArrayableValue($config['imageGeneration'] ?? null),
                'canvasProvider' => $this->normalizeArrayableValue($config['canvasProvider'] ?? null),
                'featureFlags' => $config['featureFlags'] ?? null,
                'expAssignments' => $this->normalizeArrayableValue($config['expAssignments'] ?? null),
                'diagnostics' => $config['diagnostics'] ?? null,
                'capi' => $this->normalizeCapiSessionOptions($config['capi'] ?? null),
                'requestPermission' => isset($config['onPermissionRequest']),
                'requestUserInput' => isset($config['onUserInputRequest']),
                'requestElicitation' => isset($config['onElicitationRequest']),
                'askUserVariant' => ($config['askUserVariant'] ?? null) instanceof \BackedEnum
                    ? $config['askUserVariant']->value
                    : ($config['askUserVariant'] ?? null),
                'requestExitPlanMode' => isset($config['onExitPlanModeRequest']),
                'requestAutoModeSwitch' => isset($config['onAutoModeSwitchRequest']),
                'hooks' => $hasHooks,
                'workingDirectory' => $config['workingDirectory'] ?? null,
                'additionalDirectories' => $config['additionalDirectories'] ?? null,
                'streaming' => $config['streaming'] ?? null,
                'includeSubAgentStreamingEvents' => $config['includeSubAgentStreamingEvents'] ?? null,
                'mcpServers' => $config['mcpServers'] ?? null,
                'envValueMode' => 'direct',
                'customAgents' => $config['customAgents'] ?? null,
                'customAgentsLocalOnly' => $config['customAgentsLocalOnly'] ?? null,
                'defaultAgent' => $config['defaultAgent'] ?? null,
                'agent' => $config['agent'] ?? null,
                'configDir' => $config['configDir'] ?? null,
                'configDirectory' => $config['configDirectory'] ?? null,
                'enableConfigDiscovery' => $config['enableConfigDiscovery'] ?? null,
                'skillDirectories' => $config['skillDirectories'] ?? null,
                'agentDirectories' => $config['agentDirectories'] ?? null,
                'hookDirectories' => $config['hookDirectories'] ?? null,
                'pluginDirectories' => $config['pluginDirectories'] ?? null,
                'instructionDirectories' => $config['instructionDirectories'] ?? null,
                'disabledSkills' => $config['disabledSkills'] ?? null,
                'disableResume' => $config['suppressResumeEvent'] ?? $config['disableResume'] ?? null,
                'continuePendingWork' => $config['continuePendingWork'] ?? null,
                'openCanvases' => $config['openCanvases'] ?? null,
                'allowTranscriptRecovery' => $config['allowTranscriptRecovery'] ?? null,
                'enableSessionTelemetry' => $config['enableSessionTelemetry'] ?? null,
                'enableGitHubTelemetryForwarding' => $this->onGitHubTelemetry !== null
                    ? true
                    : ($config['enableGitHubTelemetryForwarding'] ?? null),
                'gitHubToken' => $config['gitHubToken'] ?? null,
                'gitHubTokenProviderRegistrationId' => $tokenProviderRegistrationId,
                'remoteSession' => $config['remoteSession'] ?? null,
                'canvases' => $config['canvases'] ?? null,
                'requestCanvasRenderer' => $config['requestCanvasRenderer'] ?? null,
                'requestExtensions' => $config['requestExtensions'] ?? null,
                'extensionInfo' => $config['extensionInfo'] ?? null,
                'largeOutput' => $config['largeOutput'] ?? null,
                'extensionSdkPath' => $config['extensionSdkPath'] ?? null,
                'requestMcpApps' => ($config['enableMcpApps'] ?? null) === true ? true : null,
                'githubMcpToolConfig' => $this->normalizeGitHubMcpToolConfig($config['githubMcpToolConfig'] ?? null),
                'mcpOAuthTokenStorage' => $config['mcpOAuthTokenStorage'] ?? null,
                'authClientIdMetadataUrl' => $config['authClientIdMetadataUrl'] ?? null,
                'skipEmbeddingRetrieval' => $config['skipEmbeddingRetrieval'] ?? null,
                'embeddingCacheStorage' => $config['embeddingCacheStorage'] ?? null,
                'organizationCustomInstructions' => $config['organizationCustomInstructions'] ?? null,
                'enableOnDemandInstructionDiscovery' => $config['enableOnDemandInstructionDiscovery'] ?? null,
                'enableFileHooks' => $config['enableFileHooks'] ?? null,
                'enableHostGitOperations' => $config['enableHostGitOperations'] ?? null,
                'enableSessionStore' => $config['enableSessionStore'] ?? null,
                'enableGitHubCacheStore' => $config['enableGitHubCacheStore'] ?? null,
                'enableSkills' => $config['enableSkills'] ?? null,
                'enableCitations' => $config['enableCitations'] ?? null,
                'excludedBuiltinAgents' => $config['excludedBuiltinAgents'] ?? null,
                'includedBuiltinAgents' => $config['includedBuiltinAgents'] ?? null,
                'sessionLimits' => $this->normalizeArrayableValue($config['sessionLimits'] ?? null),
                'enableManagedSettings' => $config['enableManagedSettings'] ?? null,
                'enforceManagedModelDefaults' => $config['enforceManagedModelDefaults'] ?? null,
                'managedSettings' => $config['managedSettings'] ?? null,
                'enableFileChangeTracking' => $config['enableFileChangeTracking'] ?? null,
                'disabledMcpServers' => $config['disabledMcpServers'] ?? null,
                'includedBuiltinSkills' => $config['includedBuiltinSkills'] ?? null,
                'hasSkillProvider' => $skillProvider !== null ? true : null,
                'hasSessionFsProvider' => $sessionFsProvider !== null ? true : null,
            ], fn ($v) => $v !== null));
        } catch (Throwable $e) {
            if ($tokenProviderRegistrationId !== null) {
                unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
            }
            unset($this->sessions[$sessionId]);
            unset($this->bearerTokenProviders[$sessionId]);
            throw $e;
        }

        $resumedSessionId = $response['sessionId'] ?? null;
        if (! is_string($resumedSessionId) || $resumedSessionId === '') {
            if ($tokenProviderRegistrationId !== null) {
                unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
            }
            unset($this->sessions[$sessionId]);
            unset($this->bearerTokenProviders[$sessionId]);

            throw new RuntimeException('Failed to resume session');
        }
        if ($resumedSessionId !== $sessionId) {
            if ($tokenProviderRegistrationId !== null) {
                unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
            }
            unset($this->sessions[$sessionId]);
            unset($this->bearerTokenProviders[$sessionId]);

            throw new RuntimeException('Runtime returned a different session ID than requested');
        }
        $workspacePath = $response['workspacePath'] ?? null;
        $capabilities = $response['capabilities'] ?? null;

        $session->setWorkspacePath($workspacePath);
        if (is_array($response['openCanvases'] ?? null)) {
            $session->setOpenCanvases($response['openCanvases']);
        }
        if (isset($response['transcriptRecovery']) && is_array($response['transcriptRecovery'])) {
            $session->setTranscriptRecovery(TranscriptRecovery::fromArray($response['transcriptRecovery']));
        }
        if ($tokenProviderRegistrationId !== null || $bearerTokenProviders !== []) {
            $session->setOnDisconnected(function () use ($tokenProviderRegistrationId, $sessionId): void {
                if ($tokenProviderRegistrationId !== null) {
                    unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
                }
                unset($this->bearerTokenProviders[$sessionId]);
            });
        }
        $session->setCapabilities($capabilities);
        $this->sessions[$resumedSessionId] = $session;

        try {
            $this->registerMcpAuthRequestInterest($resumedSessionId, $config);

            if (! empty($config['mcpServers'])) {
                $this->rpcClient->request('session.mcp.reloadWithConfig', [
                    'sessionId' => $resumedSessionId,
                    'config' => ['mcpServers' => $config['mcpServers']],
                ]);
            }

            $this->applyPostCreateOptionsPatch($resumedSessionId, $session, $config);
        } catch (Throwable $e) {
            unset($this->sessions[$resumedSessionId]);
            unset($this->bearerTokenProviders[$resumedSessionId]);
            if ($tokenProviderRegistrationId !== null) {
                unset($this->gitHubTokenProviders[$tokenProviderRegistrationId]);
            }
            throw $e;
        }

        ResumeSession::dispatch($session);

        return $session;
    }

    /**
     * Normalize the githubMcpToolConfig option into a plain array for the RPC payload.
     */
    private function normalizeGitHubMcpToolConfig(GitHubMcpToolConfig|array|null $config): ?array
    {
        if ($config === null) {
            return null;
        }

        return $config instanceof GitHubMcpToolConfig ? $config->toArray() : $config;
    }

    /**
     * Normalize provider-scoped CAPI options into a plain array for the RPC payload.
     */
    private function normalizeCapiSessionOptions(CapiSessionOptions|array|null $config): ?array
    {
        if ($config === null) {
            return null;
        }

        return $config instanceof CapiSessionOptions ? $config->toArray() : $config;
    }

    private function normalizeArrayableValue(mixed $value): mixed
    {
        if ($value instanceof Arrayable) {
            return $this->normalizeArrayableValue($value->toArray());
        }

        if (! is_array($value)) {
            return $value;
        }

        return array_map(fn ($item) => $this->normalizeArrayableValue($item), $value);
    }

    /**
     * @return array{array, array<string, callable(array{providerName: string, sessionId: string}): string>}
     */
    private function extractBearerTokenProviders(array $config): array
    {
        $providers = [];
        $provider = $config['provider'] ?? null;
        $providerConfig = $provider instanceof ProviderConfig ? $provider->toArray() : $provider;
        $bearerTokenProvider = $provider instanceof ProviderConfig
            ? $provider->bearerTokenProvider
            : (is_array($provider) ? ($provider['bearerTokenProvider'] ?? null) : null);

        if ($bearerTokenProvider !== null) {
            if (! is_callable($bearerTokenProvider)) {
                throw new InvalidArgumentException('provider.bearerTokenProvider must be callable');
            }

            $providers['default'] = $bearerTokenProvider;
            unset($providerConfig['bearerTokenProvider']);
            $providerConfig['hasBearerTokenProvider'] = true;
            $config['provider'] = $providerConfig;
        }

        if (is_array($config['providers'] ?? null)) {
            foreach ($config['providers'] as $index => $namedProvider) {
                $namedProviderConfig = $namedProvider instanceof NamedProviderConfig
                    ? $namedProvider->toArray()
                    : $namedProvider;
                $bearerTokenProvider = $namedProvider instanceof NamedProviderConfig
                    ? $namedProvider->bearerTokenProvider
                    : (is_array($namedProvider) ? ($namedProvider['bearerTokenProvider'] ?? null) : null);

                if ($bearerTokenProvider === null) {
                    continue;
                }

                if (! is_callable($bearerTokenProvider)) {
                    throw new InvalidArgumentException("providers.{$index}.bearerTokenProvider must be callable");
                }

                $providerName = $namedProvider instanceof NamedProviderConfig
                    ? $namedProvider->name
                    : ($namedProvider['name'] ?? null);

                if (! is_string($providerName)) {
                    throw new InvalidArgumentException("providers.{$index}.name must be a string");
                }

                $providers[$providerName] = $bearerTokenProvider;
                unset($namedProviderConfig['bearerTokenProvider']);
                $namedProviderConfig['hasBearerTokenProvider'] = true;
                $config['providers'][$index] = $namedProviderConfig;
            }
        }

        return [$config, $providers];
    }

    private function registerGitHubTokenProvider(?callable $provider, ?string $sessionId = null): ?string
    {
        if ($provider === null) {
            return null;
        }

        $registrationId = (string) Str::uuid();
        $this->gitHubTokenProviders[$registrationId] = [
            'provider' => $provider,
            'sessionId' => $sessionId,
        ];

        return $registrationId;
    }

    /**
     * Register session handlers before the RPC whenever the session ID is known.
     */
    private function registerSessionHandlers(Session $session, array $tools, array $commands, array $config, mixed $hooks): void
    {
        $session->registerTools($tools);
        $session->registerCommands($commands);
        $session->registerPermissionHandler($config['onPermissionRequest']);

        if (isset($config['canvasHandler']) || isset($config['canvases'])) {
            $session->registerCanvasHandler($config['canvasHandler'] ?? null, $config['canvases'] ?? null);
        }

        foreach ([
            'onUserInputRequest' => 'registerUserInputHandler',
            'onElicitationRequest' => 'registerElicitationHandler',
            'onExitPlanModeRequest' => 'registerExitPlanModeHandler',
            'onAutoModeSwitchRequest' => 'registerAutoModeSwitchHandler',
            'onMcpAuthRequest' => 'registerMcpAuthHandler',
        ] as $option => $method) {
            if (isset($config[$option]) && is_callable($config[$option])) {
                $session->{$method}($config[$option]);
            }
        }

        if ($hooks !== null) {
            $session->registerHooks($hooks);
        }

        if (isset($config['onEvent']) && is_callable($config['onEvent'])) {
            $session->on($config['onEvent']);
        }
    }

    private function validateCanvasHandler(mixed $handler): void
    {
        if ($handler !== null && ! $handler instanceof CanvasHandler) {
            throw new InvalidArgumentException('canvasHandler must be an instance of '.CanvasHandler::class.'.');
        }
    }

    private function normalizeCanvasDeclarations(mixed $canvases): ?array
    {
        if ($canvases === null) {
            return null;
        }

        if (! is_array($canvases)) {
            throw new InvalidArgumentException('canvases must be an array of CanvasDeclaration instances or arrays.');
        }

        $declarations = [];

        foreach ($canvases as $canvas) {
            if ($canvas instanceof CanvasDeclaration) {
                $declaration = $canvas;
            } elseif (is_array($canvas)) {
                $declaration = CanvasDeclaration::fromArray($canvas);
            } else {
                throw new InvalidArgumentException('Each canvas must be a CanvasDeclaration instance or an array.');
            }

            if ($declaration->id === '') {
                throw new InvalidArgumentException('Canvas declaration ids must not be empty.');
            }

            if (array_key_exists($declaration->id, $declarations)) {
                throw new InvalidArgumentException("Canvas id \"{$declaration->id}\" is declared more than once.");
            }

            $declarations[$declaration->id] = $declaration->toArray();
        }

        return array_values($declarations);
    }

    private function registerMcpAuthRequestInterest(string $sessionId, array $config): void
    {
        if (! isset($config['onMcpAuthRequest']) || ! is_callable($config['onMcpAuthRequest'])) {
            return;
        }

        $this->rpcClient->request('session.eventLog.registerInterest', [
            'sessionId' => $sessionId,
            'eventType' => 'mcp.oauth_required',
        ]);
    }

    private function acquireGitHubToken(array $params): array
    {
        $registrationId = $params['registrationId'] ?? null;
        $registration = $registrationId !== null ? ($this->gitHubTokenProviders[$registrationId] ?? null) : null;

        if ($registration === null) {
            throw new RuntimeException("No GitHub token provider registered for registration ID \"{$registrationId}\"");
        }

        $result = ($registration['provider'])([
            'host' => $params['host'] ?? '',
            'sessionId' => $params['sessionId'] ?? $registration['sessionId'],
            'reason' => $params['reason'] ?? 'initial',
        ]);

        if ($result instanceof Arrayable) {
            $result = $result->toArray();
        }

        if (! is_array($result)) {
            throw new \UnexpectedValueException('GitHub token provider must return an array result');
        }

        return $result;
    }

    private function acquireProviderToken(array $params): array
    {
        $request = ProviderTokenAcquireRequest::fromArray($params);
        $provider = $this->bearerTokenProviders[$request->sessionId][$request->providerName] ?? null;

        if ($provider === null) {
            throw new RuntimeException(
                "No bearer-token provider registered for provider \"{$request->providerName}\"",
            );
        }

        $token = $provider([
            'providerName' => $request->providerName,
            'sessionId' => $request->sessionId,
        ]);

        return (new ProviderTokenAcquireResult(token: $token))->toArray();
    }

    protected function forgetGitHubTokenProviderForSession(string $sessionId): void
    {
        unset($this->bearerTokenProviders[$sessionId]);

        foreach ($this->gitHubTokenProviders as $registrationId => $registration) {
            if ($registration['sessionId'] === $sessionId) {
                unset($this->gitHubTokenProviders[$registrationId]);
            }
        }
    }

    private function applyPostCreateOptionsPatch(string $sessionId, Session $session, array $config): void
    {
        $patch = array_filter([
            'skipCustomInstructions' => $config['skipCustomInstructions'] ?? (
                $this->mode === CopilotClientMode::Empty ? true : null
            ),
            'customAgentsLocalOnly' => $config['customAgentsLocalOnly'] ?? (
                $this->mode === CopilotClientMode::Empty ? true : null
            ),
            'coauthorEnabled' => $config['coauthorEnabled'] ?? (
                $this->mode === CopilotClientMode::Empty ? false : null
            ),
            'manageScheduleEnabled' => $config['manageScheduleEnabled'] ?? (
                $this->mode === CopilotClientMode::Empty ? false : null
            ),
            'suppressCustomAgentPrompt' => $config['suppressCustomAgentPrompt'] ?? null,
            'installedPlugins' => $this->mode === CopilotClientMode::Empty ? [] : null,
            'includedBuiltinSkills' => $this->mode === CopilotClientMode::Empty
                ? ($config['includedBuiltinSkills'] ?? [])
                : null,
        ], fn ($value) => $value !== null);

        if ($patch === []) {
            return;
        }

        try {
            $response = $this->rpcClient->request('session.options.update', [
                'sessionId' => $sessionId,
                ...$patch,
            ]);

            if (($response['success'] ?? true) === false) {
                throw new RuntimeException('Failed to update session options');
            }
        } catch (Throwable $e) {
            unset($this->sessions[$sessionId]);

            try {
                $session->disconnect();
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }

            throw $e;
        }
    }

    /**
     * Normalize a commands array into the format expected by the JSON-RPC request.
     *
     * Accepts plain arrays, objects with toArray(), or JsonSerializable objects.
     * Entries missing a 'name' key are filtered out.
     *
     * @param  array<array{name: string, handler: Closure}|CommandDefinition>  $commands
     * @return list<array{name: string, description?: string}>|null
     */
    private function normalizeCommandsForRequest(array $commands): ?array
    {
        $normalized = array_map(function ($cmd) {
            if ($cmd instanceof Arrayable) {
                $cmd = $cmd->toArray();
            }

            if (! is_array($cmd) || ! isset($cmd['name']) || ! is_string($cmd['name'])) {
                return null;
            }

            $name = trim($cmd['name']);
            if ($name === '') {
                return null;
            }

            $description = null;
            if (array_key_exists('description', $cmd) && is_string($cmd['description'])) {
                $description = $cmd['description'];
            }

            $result = ['name' => $name];
            if ($description !== null) {
                $result['description'] = $description;
            }

            return $result;
        }, $commands);

        return array_values(array_filter($normalized)) ?: null;
    }

    private function normalizeSessionConfigAliases(array $config): array
    {
        if (! isset($config['onMcpAuthRequest']) && isset($config['on_mcp_auth_request'])) {
            $config['onMcpAuthRequest'] = $config['on_mcp_auth_request'];
        }

        if (! isset($config['onExitPlanModeRequest']) && isset($config['onExitPlanMode'])) {
            $config['onExitPlanModeRequest'] = $config['onExitPlanMode'];
        }

        if (! isset($config['onAutoModeSwitchRequest']) && isset($config['onAutoModeSwitch'])) {
            $config['onAutoModeSwitchRequest'] = $config['onAutoModeSwitch'];
        }

        if (! isset($config['disableResume']) && isset($config['suppressResumeEvent'])) {
            $config['disableResume'] = $config['suppressResumeEvent'];
        }

        return $config;
    }

    private function normalizeToolFilters(array $config): array
    {
        foreach (['availableTools', 'excludedTools'] as $field) {
            $filters = $config[$field] ?? null;

            if ($filters === null) {
                continue;
            }

            if ($filters instanceof ToolSet) {
                $filters = $filters->toArray();
            } elseif (is_string($filters)) {
                throw new InvalidArgumentException(
                    "{$field} must be a ToolSet or array of source-qualified filters, not a string.",
                );
            } elseif (! is_array($filters)) {
                throw new InvalidArgumentException("{$field} must be a ToolSet or array of source-qualified filters.");
            }

            $filters = array_values($filters);

            foreach ($filters as $filter) {
                if (! is_string($filter)) {
                    throw new InvalidArgumentException("{$field} entries must be strings.");
                }
                if ($filter === '*') {
                    throw new InvalidArgumentException(
                        "Invalid {$field} entry '*': there is no bare wildcard. Use a source-qualified filter such as 'builtin:*'.",
                    );
                }
            }

            $config[$field] = $filters;
        }

        return $config;
    }

    private function applyModeDefaults(array $config): array
    {
        if ($this->mode !== CopilotClientMode::Empty) {
            return $config;
        }

        if (($config['availableTools'] ?? null) === null) {
            throw new InvalidArgumentException('availableTools is required when mode is "empty".');
        }

        foreach ([
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
            'enableExperimentalMode' => false,
        ] as $key => $default) {
            if (($config[$key] ?? null) === null) {
                $config[$key] = $default;
            }
        }

        $config['systemMessage'] = $this->systemMessageForEmptyMode($config['systemMessage'] ?? null);

        return $config;
    }

    private function systemMessageForEmptyMode(mixed $systemMessage): array
    {
        if ($systemMessage instanceof Arrayable) {
            $systemMessage = $systemMessage->toArray();
        }

        if ($systemMessage === null) {
            return [
                'mode' => 'customize',
                'sections' => ['environment_context' => ['action' => 'remove']],
            ];
        }

        if (! is_array($systemMessage)) {
            throw new InvalidArgumentException('systemMessage must be an array or Arrayable value.');
        }

        if (($systemMessage['mode'] ?? null) === 'replace') {
            return $systemMessage;
        }

        $sections = $systemMessage['sections'] ?? [];
        $sections = is_array($sections) ? $sections : [];

        if (($systemMessage['mode'] ?? null) === 'customize') {
            if (array_key_exists('environment_context', $sections)) {
                return $systemMessage;
            }

            $systemMessage['sections'] = [
                ...$sections,
                'environment_context' => ['action' => 'remove'],
            ];

            return $systemMessage;
        }

        return array_filter([
            'mode' => 'customize',
            'content' => $systemMessage['content'] ?? null,
            'sections' => ['environment_context' => ['action' => 'remove']],
        ], fn ($value) => $value !== null);
    }

    /**
     * Get the current connection state.
     */
    public function getState(): ConnectionState
    {
        return $this->state;
    }

    /**
     * Send a ping to verify connectivity.
     *
     * @return array{message: string, timestamp: int, protocolVersion?: int}
     *
     * @throws JsonRpcException
     */
    public function ping(?string $message = null): array
    {
        $this->ensureConnected();

        return tap(
            $this->rpcClient->request('ping', array_filter([
                'message' => $message,
            ]), timeout: 10.0),
            fn (array $response) => PingPong::dispatch($response),
        );
    }

    /**
     * Get CLI status including version and protocol information.
     *
     * @throws JsonRpcException
     */
    public function getStatus(): GetStatusResponse
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('status.get');

        return GetStatusResponse::fromArray($response);
    }

    /**
     * Get current authentication status.
     *
     * @throws JsonRpcException
     */
    public function getAuthStatus(): GetAuthStatusResponse
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('auth.getStatus', []);

        return GetAuthStatusResponse::fromArray($response);
    }

    /**
     * Ensure the client is connected.
     *
     * @throws RuntimeException
     */
    protected function ensureConnected(): void
    {
        if ($this->state !== ConnectionState::CONNECTED || $this->rpcClient === null) {
            throw new RuntimeException('Client not connected. Call start() first.');
        }
    }

    /**
     * Verify the server's protocol version matches the SDK.
     *
     * @throws RuntimeException|JsonRpcException
     */
    protected function verifyProtocolVersion(): void
    {
        $expectedVersion = Protocol::version();

        try {
            $clientInfo = data_get($this->options, 'client_info', data_get($this->options, 'clientInfo'));
            $clientInfo = $clientInfo instanceof Arrayable ? $clientInfo->toArray() : $clientInfo;
            $serverVersion = $this->rpc()->connect(array_filter([
                'clientInfo' => $clientInfo,
                'enableGitHubTelemetryForwarding' => $this->onGitHubTelemetry !== null ? true : null,
                'token' => $this->connectionToken,
                'supportedTaskKinds' => [TaskKind::AGENT->value, TaskKind::CLIENT->value, TaskKind::SHELL->value],
            ], fn ($value) => $value !== null))->protocolVersion;
        } catch (JsonRpcException $e) {
            if ($e->code === -32601 || str_contains($e->getMessage(), 'Unhandled method connect')) {
                $serverVersion = $this->ping()['protocolVersion'] ?? null;
            } else {
                throw $e;
            }
        }

        if ($serverVersion === null) {
            throw new RuntimeException(
                "SDK protocol version mismatch: SDK expects version {$expectedVersion}, ".
                'but server does not report a protocol version.',
            );
        }

        if ($serverVersion !== $expectedVersion) {
            throw new RuntimeException(
                "SDK protocol version mismatch: SDK expects version {$expectedVersion}, ".
                "but server reports version {$serverVersion}.",
            );
        }
    }

    /**
     * Handle incoming notifications from the server.
     */
    protected function handleNotification(string $method, array $params): void
    {
        if ($method === 'session.event') {
            $sessionId = $params['sessionId'] ?? null;
            $eventData = $params['event'] ?? null;

            if ($sessionId !== null && $eventData !== null && isset($this->sessions[$sessionId])) {
                $event = SessionEvent::fromArray($eventData);
                $this->sessions[$sessionId]->dispatchEvent($event);
            }
        }

        if ($method === 'session.lifecycle') {
            $this->handleLifecycleNotification($params);
        }

        if ($method === 'gitHubTelemetry.event' && $this->onGitHubTelemetry !== null) {
            $notification = GitHubTelemetryNotification::fromArray($params);

            try {
                ($this->onGitHubTelemetry)($notification);
            } catch (Throwable) {
                // Telemetry handlers must not interrupt RPC notification processing.
            }
        }
    }
}
