<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Types\ForegroundSessionInfo;
use Revolution\Copilot\Types\SessionLifecycleEvent;
use Revolution\Copilot\Types\SessionListFilter;
use Revolution\Copilot\Types\SessionMetadata;
use RuntimeException;
use Throwable;

/**
 * Session management operations beyond create/resume:
 * listing, deletion, foreground control, and lifecycle events.
 */
trait ManagesSessions
{
    /**
     * Session lifecycle event handlers.
     *
     * @var array<callable(SessionLifecycleEvent): void>
     */
    protected array $lifecycleHandlers = [];

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
     * Returns metadata about each session including ID, timestamps, summary, and context.
     *
     * @param  SessionListFilter|array{cwd?: string, gitRoot?: string, repository?: string, branch?: string}|null  $filter  Optional filter to limit returned sessions by context fields
     * @return array<SessionMetadata>
     *
     * @throws JsonRpcException
     *
     * @example
     * // List all sessions
     * $sessions = $client->listSessions();
     *
     * // List sessions for a specific repository
     * $sessions = $client->listSessions(['repository' => 'owner/repo']);
     *
     * // List sessions in a specific working directory
     * $sessions = $client->listSessions(new SessionListFilter(cwd: '/path/to/project'));
     */
    public function listSessions(SessionListFilter|array|null $filter = null): array
    {
        $this->ensureConnected();

        $filterArray = match (true) {
            $filter instanceof SessionListFilter => $filter->toArray(),
            is_array($filter) => array_filter($filter, fn ($v) => $v !== null),
            default => [],
        };

        $response = $this->rpcClient->request('session.list', array_filter([
            'filter' => $filterArray ?: null,
        ]));

        return array_map(
            fn (array $session) => SessionMetadata::fromArray($session),
            $response['sessions'] ?? [],
        );
    }

    /**
     * Get metadata for a specific session by ID.
     *
     * This provides an efficient O(1) lookup of a single session's metadata
     * instead of listing all sessions. Returns null if the session is not found.
     *
     * @throws JsonRpcException
     *
     * @example
     * $metadata = $client->getSessionMetadata('session-123');
     * if ($metadata) {
     *     echo "Session started at: {$metadata->startTime}";
     * }
     */
    public function getSessionMetadata(string $sessionId): ?SessionMetadata
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('session.getMetadata', [
            'sessionId' => $sessionId,
        ]);

        $session = $response['session'] ?? null;

        if ($session === null) {
            return null;
        }

        return SessionMetadata::fromArray($session);
    }

    /**
     * Gets the foreground session ID in TUI+server mode.
     *
     * This returns the ID of the session currently displayed in the TUI.
     * Only available when connecting to a server running in TUI+server mode (--ui-server).
     *
     * @throws JsonRpcException
     */
    public function getForegroundSessionId(): ?string
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('session.getForeground', []);

        return ForegroundSessionInfo::fromArray($response)->sessionId;
    }

    /**
     * Sets the foreground session in TUI+server mode.
     *
     * This requests the TUI to switch to displaying the specified session.
     * Only available when connecting to a server running in TUI+server mode (--ui-server).
     *
     * @throws RuntimeException|JsonRpcException
     */
    public function setForegroundSessionId(string $sessionId): void
    {
        $this->ensureConnected();

        $response = $this->rpcClient->request('session.setForeground', [
            'sessionId' => $sessionId,
        ]);

        if (! ($response['success'] ?? false)) {
            throw new RuntimeException($response['error'] ?? 'Failed to set foreground session');
        }
    }

    /**
     * Subscribes to session lifecycle events.
     *
     * Lifecycle events are emitted when sessions are created, deleted, updated,
     * or change foreground/background state (in TUI+server mode).
     *
     * @param  callable(SessionLifecycleEvent): void  $handler
     * @return callable(): void A function that, when called, unsubscribes the handler
     */
    public function onLifecycle(callable $handler): callable
    {
        $this->lifecycleHandlers[] = $handler;

        return function () use ($handler) {
            $this->lifecycleHandlers = array_filter(
                $this->lifecycleHandlers,
                fn ($h) => $h !== $handler,
            );
        };
    }

    /**
     * Handle session lifecycle notifications.
     */
    protected function handleLifecycleNotification(array $params): void
    {
        // Validate required fields
        if (! isset($params['type']) || ! is_string($params['type'])) {
            return;
        }

        if (! isset($params['sessionId']) || ! is_string($params['sessionId'])) {
            return;
        }

        try {
            $event = SessionLifecycleEvent::fromArray($params);

            // Dispatch to all registered handlers
            foreach ($this->lifecycleHandlers as $handler) {
                try {
                    $handler($event);
                } catch (Throwable) {
                    // Ignore handler errors
                }
            }
        } catch (Throwable) {
            // Ignore parsing errors for invalid event types
        }
    }
}
