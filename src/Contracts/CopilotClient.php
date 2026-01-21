<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\JsonRpc\JsonRpcException;
use RuntimeException;

/**
 * Main client for interacting with the Copilot CLI.
 */
interface CopilotClient
{
    /**
     * Start the CLI server and establish connection.
     *
     * @throws RuntimeException
     */
    public function start(): void;

    /**
     * Create a new conversation session.
     *
     * @param  array{session_id?: string, model?: string, tools?: array, system_message?: array, available_tools?: array, excluded_tools?: array, provider?: array, on_permission_request?: callable, streaming?: bool, mcp_servers?: array, custom_agents?: array}  $config
     *
     * @throws RuntimeException
     */
    public function createSession(array $config = []): CopilotSession;

    /**
     * Send a ping to verify connectivity.
     *
     * @return array{message: string, timestamp: int, protocolVersion?: int}
     *
     * @throws RuntimeException|JsonRpcException
     */
    public function ping(?string $message = null): array;
}
