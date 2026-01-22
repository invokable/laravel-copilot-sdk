<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

/**
 * Main client for interacting with the Copilot CLI.
 */
interface CopilotClient
{
    /**
     * Start the CLI server and establish connection.
     */
    public function start(): static;

    /**
     * Create a new conversation session.
     *
     * @throws JsonRpcException
     */
    public function createSession(SessionConfig|array $config = []): CopilotSession;

    /**
     * Resume an existing session.
     *
     * @throws JsonRpcException
     */
    public function resumeSession(string $sessionId, ResumeSessionConfig|array $config = []): CopilotSession;

    /**
     * Send a ping to verify connectivity.
     *
     * @return array{message: string, timestamp: int, protocolVersion?: int}
     *
     * @throws JsonRpcException
     */
    public function ping(?string $message = null): array;
}
