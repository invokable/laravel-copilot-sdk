<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Types\ConnectionState;
use Revolution\Copilot\Types\SessionEvent;
use RuntimeException;

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
        // Validate mutually exclusive options
        if (isset($options['cli_url']) && (isset($options['cli_path']) || isset($options['use_stdio']))) {
            throw new \InvalidArgumentException('cli_url is mutually exclusive with cli_path and use_stdio');
        }

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
     * @throws RuntimeException
     */
    public function start(): void
    {
        if ($this->state === ConnectionState::CONNECTED) {
            return;
        }

        $this->state = ConnectionState::CONNECTING;

        try {
            // Start the CLI process
            $this->processManager->start();

            // Create JSON-RPC client
            $this->rpcClient = app(JsonRpcClient::class, [
                'stdin' => $this->processManager->getStdin(),
                'stdout' => $this->processManager->getStdout(),
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
        } catch (\Throwable $e) {
            $this->state = ConnectionState::ERROR;
            throw $e;
        }
    }

    /**
     * Stop the CLI server and close all sessions.
     *
     * @return array<\Throwable> Errors encountered during cleanup
     */
    public function stop(): array
    {
        $errors = [];

        // Destroy all sessions
        foreach ($this->sessions as $session) {
            try {
                $session->destroy();
            } catch (\Throwable $e) {
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
     * @param  array{session_id?: string, model?: string, tools?: array, system_message?: array, available_tools?: array, excluded_tools?: array, provider?: array, on_permission_request?: callable, streaming?: bool, mcp_servers?: array, custom_agents?: array, config_dir?: string, skill_directories?: array, disabled_skills?: array}  $config
     *
     * @throws RuntimeException
     */
    public function createSession(array $config = []): Session
    {
        $this->ensureConnected();

        $tools = $config['tools'] ?? [];
        $toolsForRequest = array_map(fn ($tool) => [
            'name' => $tool['name'],
            'description' => $tool['description'] ?? null,
            'parameters' => $tool['parameters'] ?? null,
        ], $tools);

        $response = $this->rpcClient->request('session.create', array_filter([
            'sessionId' => $config['session_id'] ?? null,
            'model' => $config['model'] ?? null,
            'tools' => $toolsForRequest ?: null,
            'systemMessage' => $config['system_message'] ?? null,
            'availableTools' => $config['available_tools'] ?? null,
            'excludedTools' => $config['excluded_tools'] ?? null,
            'provider' => $config['provider'] ?? null,
            'requestPermission' => isset($config['on_permission_request']),
            'streaming' => $config['streaming'] ?? null,
            'mcpServers' => $config['mcp_servers'] ?? null,
            'customAgents' => $config['custom_agents'] ?? null,
            'configDir' => $config['config_dir'] ?? null,
            'skillDirectories' => $config['skill_directories'] ?? null,
            'disabledSkills' => $config['disabled_skills'] ?? null,
        ], fn ($v) => $v !== null));

        $sessionId = $response['sessionId'] ?? throw new RuntimeException('Failed to create session');

        $session = app(Session::class, [
            'sessionId' => $sessionId,
            'client' => $this->rpcClient,
        ]);
        $session->registerTools($tools);

        if (isset($config['on_permission_request'])) {
            $session->registerPermissionHandler($config['on_permission_request']);
        }

        $this->sessions[$sessionId] = $session;

        return $session;
    }

    /**
     * Resume an existing session.
     *
     * @param  array{tools?: array, provider?: array, on_permission_request?: callable, streaming?: bool, mcp_servers?: array, custom_agents?: array, skill_directories?: array, disabled_skills?: array}  $config
     *
     * @throws RuntimeException
     */
    public function resumeSession(string $sessionId, array $config = []): CopilotSession
    {
        $this->ensureConnected();

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
            'requestPermission' => isset($config['on_permission_request']),
            'streaming' => $config['streaming'] ?? null,
            'mcpServers' => $config['mcp_servers'] ?? null,
            'customAgents' => $config['custom_agents'] ?? null,
            'skillDirectories' => $config['skill_directories'] ?? null,
            'disabledSkills' => $config['disabled_skills'] ?? null,
        ], fn ($v) => $v !== null));

        $resumedSessionId = $response['sessionId'] ?? throw new RuntimeException('Failed to resume session');

        $session = app(Session::class, [
            'sessionId' => $resumedSessionId,
            'client' => $this->rpcClient,
        ]);
        $session->registerTools($tools);

        if (isset($config['on_permission_request'])) {
            $session->registerPermissionHandler($config['on_permission_request']);
        }

        $this->sessions[$resumedSessionId] = $session;

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
     * @throws RuntimeException|JsonRpcException
     */
    public function ping(?string $message = null): array
    {
        $this->ensureConnected();

        return $this->rpcClient->request('ping', array_filter([
            'message' => $message,
        ], fn ($v) => $v !== null), timeout: 10.0);
    }

    /**
     * Get the last session ID.
     *
     * @throws RuntimeException
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
     * @throws RuntimeException
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
     * @throws RuntimeException
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
        $expectedVersion = ProcessManager::getProtocolVersion();
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
        } catch (\Throwable $e) {
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
