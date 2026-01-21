<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Exceptions\JsonRpcException;
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
     * @throws RuntimeException
     */
    public function createSession(array $config = []): CopilotSession;

    /**
     * Resume an existing session.
     *
     * @throws RuntimeException
     */
    public function resumeSession(string $sessionId, array $config = []): CopilotSession;

    /**
     * Send a ping to verify connectivity.
     *
     * @return array{message: string, timestamp: int, protocolVersion?: int}
     *
     * @throws RuntimeException|JsonRpcException
     */
    public function ping(?string $message = null): array;
}
