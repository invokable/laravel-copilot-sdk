<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Types\GetAuthStatusResponse;
use Revolution\Copilot\Types\GetStatusResponse;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionLifecycleEvent;
use Revolution\Copilot\Types\SessionListFilter;
use Revolution\Copilot\Types\SessionMetadata;
use Throwable;

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

    /**
     * Get CLI status including version and protocol information.
     *
     * @throws JsonRpcException
     */
    public function getStatus(): GetStatusResponse;

    /**
     * Get current authentication status.
     *
     * @throws JsonRpcException
     */
    public function getAuthStatus(): GetAuthStatusResponse;

    /**
     * List available models with their metadata.
     *
     * @return array<ModelInfo>
     *
     * @throws JsonRpcException
     */
    public function listModels(): array;

    /**
     * List all available sessions.
     *
     * @param  SessionListFilter|array{cwd?: string, gitRoot?: string, repository?: string, branch?: string}|null  $filter
     * @return array<SessionMetadata>
     *
     * @throws JsonRpcException
     */
    public function listSessions(SessionListFilter|array|null $filter = null): array;

    /**
     * Gets the foreground session ID in TUI+server mode.
     *
     * @throws JsonRpcException
     */
    public function getForegroundSessionId(): ?string;

    /**
     * Sets the foreground session in TUI+server mode.
     *
     * @throws JsonRpcException
     */
    public function setForegroundSessionId(string $sessionId): void;

    /**
     * Subscribes to session lifecycle events.
     *
     * @param  callable(SessionLifecycleEvent): void  $handler
     * @return callable(): void A function that, when called, unsubscribes the handler
     */
    public function onLifecycle(callable $handler): callable;

    /**
     * Stop the CLI server and close all sessions.
     *
     * @return array<Throwable> Errors encountered during cleanup
     */
    public function stop(): array;
}
