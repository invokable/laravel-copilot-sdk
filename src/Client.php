<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Events\Client\ClientStarted;
use Revolution\Copilot\Events\Client\PingPong;
use Revolution\Copilot\Events\Session\CreateSession;
use Revolution\Copilot\Events\Session\ResumeSession;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\GetAuthStatusResponse;
use Revolution\Copilot\Types\GetStatusResponse;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use RuntimeException;
use Throwable;

/**
 * Main client for interacting with the Copilot CLI.
 */
class Client implements CopilotClient
{
    protected ProcessManager $processManager;

    protected ?JsonRpcClient $rpcClient = null;

    protected ConnectionState $state = ConnectionState::DISCONNECTED;

    /**
     * Active sessions.
     *
     * @var array<string, Session>
     */
    protected array $sessions = [];

    /**
     * Create a new CopilotClient.
     *
     * @param  array{cli_path?: string, cli_args?: array, cwd?: string, log_level?: string, cli_url?: string, auto_start?: bool, auto_restart?: bool, env?: array}  $options
     */
    public function __construct(array $options = [])
    {
        $this->processManager = app(ProcessManager::class, [
            'cliPath' => $options['cli_path'] ?? null,
            'cliArgs' => $options['cli_args'] ?? [],
            'cwd' => $options['cwd'] ?? null,
            'logLevel' => $options['log_level'] ?? 'info',
            'env' => $options['env'] ?? null,
        ]);
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
            // Start the CLI process
            $this->processManager->start();

            // Create JSON-RPC client
            $this->rpcClient = app(JsonRpcClient::class, [
                'transport' => $this->processManager->getStdioTransport(),
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

            $this->state = ConnectionState::CONNECTED;

            // Verify protocol version
            $this->verifyProtocolVersion();

            ClientStarted::dispatch($this);

            return $this;
        } catch (Throwable $e) {
            $this->state = ConnectionState::ERROR;
            throw $e;
        }
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
                $session->destroy();
            } catch (Throwable $e) {
                $errors[] = $e;
            }
        }

        $this->sessions = [];

        // Stop JSON-RPC client
        if ($this->rpcClient !== null) {
            $this->rpcClient->stop();
            $this->rpcClient = null;
        }

        // Stop process
        $this->processManager->stop();

        $this->state = ConnectionState::DISCONNECTED;

        return $errors;
    }

    /**
     * Create a new conversation session.
     *
     * @param  SessionConfig|array{session_id?: string, model?: string, tools?: array, system_message?: array, available_tools?: array, excluded_tools?: array, provider?: array, on_permission_request?: callable, streaming?: bool, mcp_servers?: array, custom_agents?: array, config_dir?: string, skill_directories?: array, disabled_skills?: array}  $config
     *
     * @throws JsonRpcException
     */
    public function createSession(SessionConfig|array $config = []): CopilotSession
    {
        $this->ensureConnected();

        $config = is_array($config) ? $config : $config->toArray();

        $tools = $config['tools'] ?? [];
        $toolsForRequest = array_map(fn ($tool) => [
            'name' => $tool['name'],
            'description' => $tool['description'] ?? null,
            'parameters' => $tool['parameters'] ?? null,
        ], $tools);

        $response = $this->rpcClient->request('session.create', array_filter([
            'sessionId' => $config['sessionId'] ?? null,
            'model' => $config['model'] ?? null,
            'tools' => $toolsForRequest ?: null,
            'systemMessage' => $config['systemMessage'] ?? null,
            'availableTools' => $config['availableTools'] ?? null,
            'excludedTools' => $config['excludedTools'] ?? null,
            'provider' => $config['provider'] ?? null,
            'requestPermission' => isset($config['onPermissionRequest']),
            'streaming' => $config['streaming'] ?? null,
            'mcpServers' => $config['mcpServers'] ?? null,
            'customAgents' => $config['customAgents'] ?? null,
            'configDir' => $config['configDir'] ?? null,
            'skillDirectories' => $config['skillDirectories'] ?? null,
            'disabledSkills' => $config['disabledSkills'] ?? null,
        ], fn ($v) => $v !== null));

        $sessionId = $response['sessionId'] ?? throw new RuntimeException('Failed to create session');

        $session = app(Session::class, [
            'sessionId' => $sessionId,
            'client' => $this->rpcClient,
        ]);
        $session->registerTools($tools);

        if (isset($config['onPermissionRequest']) && is_callable($config['onPermissionRequest'])) {
            $session->registerPermissionHandler($config['onPermissionRequest']);
        }

        $this->sessions[$sessionId] = $session;

        CreateSession::dispatch($session);

        return $session;
    }

    /**
     * Resume an existing session.
     *
     * @param  ResumeSessionConfig|array{tools?: array, provider?: array, on_permission_request?: callable, streaming?: bool, mcp_servers?: array, custom_agents?: array, skill_directories?: array, disabled_skills?: array}  $config
     *
     * @throws JsonRpcException
     */
    public function resumeSession(string $sessionId, ResumeSessionConfig|array $config = []): CopilotSession
    {
        $this->ensureConnected();

        $config = is_array($config) ? $config : $config->toArray();

        $tools = $config['tools'] ?? [];
        $toolsForRequest = array_map(fn ($tool) => [
            'name' => $tool['name'],
            'description' => $tool['description'] ?? null,
            'parameters' => $tool['parameters'] ?? null,
        ], $tools);

        $response = $this->rpcClient->request('session.resume', array_filter([
            'sessionId' => $sessionId,
            'tools' => $toolsForRequest ?: null,
            'provider' => $config['provider'] ?? null,
            'requestPermission' => isset($config['onPermissionRequest']),
            'streaming' => $config['streaming'] ?? null,
            'mcpServers' => $config['mcpServers'] ?? null,
            'customAgents' => $config['customAgents'] ?? null,
            'skillDirectories' => $config['skillDirectories'] ?? null,
            'disabledSkills' => $config['disabledSkills'] ?? null,
        ], fn ($v) => $v !== null));

        $resumedSessionId = $response['sessionId'] ?? throw new RuntimeException('Failed to resume session');

        $session = app(Session::class, [
            'sessionId' => $resumedSessionId,
            'client' => $this->rpcClient,
        ]);
        $session->registerTools($tools);

        if (isset($config['onPermissionRequest']) && is_callable($config['onPermissionRequest'])) {
            $session->registerPermissionHandler($config['onPermissionRequest']);
        }

        $this->sessions[$resumedSessionId] = $session;

        ResumeSession::dispatch($session);

        return $session;
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
            ], fn ($v) => $v !== null), timeout: 10.0),
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

        $response = $this->rpcClient->request('status.get', []);

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
     * List available models with their metadata.
     *
     * @return array<ModelInfo>
     *
     * @throws JsonRpcException
     */
    public function listModels(): array
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('models.list', []);

        return array_map(
            fn (array $model) => ModelInfo::fromArray($model),
            $response['models'] ?? [],
        );
    }

    /**
     * Get the last session ID.
     *
     * @throws JsonRpcException
     */
    public function getLastSessionId(): ?string
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('session.getLastId', []);

        return $response['sessionId'] ?? null;
    }

    /**
     * Delete a session.
     *
     * @throws JsonRpcException
     */
    public function deleteSession(string $sessionId): void
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('session.delete', [
            'sessionId' => $sessionId,
        ]);

        if (! ($response['success'] ?? false)) {
            throw new RuntimeException('Failed to delete session: '.($response['error'] ?? 'Unknown error'));
        }

        unset($this->sessions[$sessionId]);
    }

    /**
     * List all available sessions.
     *
     * @return array<array{sessionId: string, startTime: string, modifiedTime: string, summary?: string, isRemote: bool}>
     *
     * @throws JsonRpcException
     */
    public function listSessions(): array
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('session.list', []);

        return $response['sessions'] ?? [];
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
        $pingResult = $this->ping();
        $serverVersion = $pingResult['protocolVersion'] ?? null;

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
    }

    /**
     * Handle tool call requests from the server.
     */
    protected function handleToolCall(array $params): array
    {
        $sessionId = $params['sessionId'] ?? null;
        $toolCallId = $params['toolCallId'] ?? null;
        $toolName = $params['toolName'] ?? null;
        $arguments = $params['arguments'] ?? [];

        if ($sessionId === null || $toolName === null) {
            return ['result' => [
                'textResultForLlm' => 'Invalid tool call parameters',
                'resultType' => 'failure',
            ]];
        }

        $session = $this->sessions[$sessionId] ?? null;

        if ($session === null) {
            return ['result' => [
                'textResultForLlm' => "Unknown session: {$sessionId}",
                'resultType' => 'failure',
            ]];
        }

        $handler = $session->getToolHandler($toolName);

        if ($handler === null) {
            return ['result' => [
                'textResultForLlm' => "Tool not supported by SDK client: {$toolName}",
                'resultType' => 'rejected',
            ]];
        }

        try {
            $invocation = [
                'sessionId' => $sessionId,
                'toolCallId' => $toolCallId,
                'toolName' => $toolName,
                'arguments' => $arguments,
            ];

            $result = $handler($arguments, $invocation);

            // Normalize result
            if (is_string($result)) {
                return ['result' => $result];
            }

            if (is_array($result) && isset($result['textResultForLlm'])) {
                return ['result' => $result];
            }

            return ['result' => [
                'textResultForLlm' => is_array($result) ? json_encode($result) : (string) $result,
                'resultType' => 'success',
            ]];
        } catch (Throwable $e) {
            return ['result' => [
                'textResultForLlm' => "Tool execution failed: {$e->getMessage()}",
                'resultType' => 'failure',
                'error' => $e->getMessage(),
            ]];
        }
    }

    /**
     * Handle permission requests from the server.
     */
    protected function handlePermissionRequest(array $params): array
    {
        $sessionId = $params['sessionId'] ?? null;
        $request = $params['permissionRequest'] ?? [];

        if ($sessionId === null) {
            return ['result' => ['kind' => 'denied-no-approval-rule-and-could-not-request-from-user']];
        }

        $session = $this->sessions[$sessionId] ?? null;

        if ($session === null) {
            return ['result' => ['kind' => 'denied-no-approval-rule-and-could-not-request-from-user']];
        }

        return ['result' => $session->handlePermissionRequest($request)];
    }
}
