<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Closure;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\SessionEventType;
use RuntimeException;

/**
 * Represents a single conversation session with the Copilot CLI.
 */
class CopilotSession
{
    /**
     * Event handlers.
     *
     * @var array<Closure(SessionEvent): void>
     */
    protected array $eventHandlers = [];

    /**
     * Tool handlers.
     *
     * @var array<string, Closure(array, array): mixed>
     */
    protected array $toolHandlers = [];

    /**
     * Permission handler.
     *
     * @var Closure(array, array): array|null
     */
    protected ?Closure $permissionHandler = null;

    public function __construct(
        public readonly string $sessionId,
        protected JsonRpcClient $client,
    ) {}

    /**
     * Send a message to this session.
     *
     * @param  array{prompt: string, attachments?: array, mode?: string}  $options
     */
    public function send(array $options): string
    {
        $response = $this->client->request('session.send', [
            'sessionId' => $this->sessionId,
            'prompt' => $options['prompt'],
            'attachments' => $options['attachments'] ?? null,
            'mode' => $options['mode'] ?? null,
        ]);

        return $response['messageId'] ?? '';
    }

    /**
     * Send a message and wait until the session becomes idle.
     *
     * @param  array{prompt: string, attachments?: array, mode?: string}  $options
     *
     * @throws RuntimeException
     */
    public function sendAndWait(array $options, float $timeout = 60.0): ?SessionEvent
    {
        $lastAssistantMessage = null;
        $idle = false;
        $error = null;

        // Register temporary event handler
        $handler = function (SessionEvent $event) use (&$lastAssistantMessage, &$idle, &$error): void {
            if ($event->isAssistantMessage()) {
                $lastAssistantMessage = $event;
            } elseif ($event->isIdle()) {
                $idle = true;
            } elseif ($event->isError()) {
                $error = $event->getErrorMessage() ?? 'Unknown error';
            }
        };

        $this->on($handler);

        try {
            // Send the message
            $this->send($options);

            // Wait for idle or error
            $endTime = microtime(true) + $timeout;

            while (! $idle && $error === null && microtime(true) < $endTime) {
                $this->client->processMessages(0.1);
            }

            if ($error !== null) {
                throw new RuntimeException("Session error: {$error}");
            }

            if (! $idle) {
                throw new RuntimeException("Timeout after {$timeout}s waiting for session.idle");
            }

            return $lastAssistantMessage;
        } finally {
            $this->off($handler);
        }
    }

    /**
     * Subscribe to events from this session.
     *
     * @param  Closure(SessionEvent): void  $handler
     * @return Closure(): void Unsubscribe function
     */
    public function on(Closure $handler): Closure
    {
        $this->eventHandlers[] = $handler;

        return fn () => $this->off($handler);
    }

    /**
     * Unsubscribe from events.
     *
     * @param  Closure(SessionEvent): void  $handler
     */
    public function off(Closure $handler): void
    {
        $this->eventHandlers = array_filter(
            $this->eventHandlers,
            fn ($h) => $h !== $handler,
        );
    }

    /**
     * Dispatch an event to all registered handlers.
     *
     * @internal
     */
    public function dispatchEvent(SessionEvent $event): void
    {
        foreach ($this->eventHandlers as $handler) {
            try {
                $handler($event);
            } catch (\Throwable) {
                // Ignore handler errors
            }
        }
    }

    /**
     * Register tool handlers.
     *
     * @param  array<array{name: string, handler: Closure}>  $tools
     *
     * @internal
     */
    public function registerTools(array $tools): void
    {
        $this->toolHandlers = [];

        foreach ($tools as $tool) {
            if (isset($tool['name'], $tool['handler'])) {
                $this->toolHandlers[$tool['name']] = $tool['handler'];
            }
        }
    }

    /**
     * Get a tool handler by name.
     *
     * @internal
     */
    public function getToolHandler(string $name): ?Closure
    {
        return $this->toolHandlers[$name] ?? null;
    }

    /**
     * Register a permission handler.
     *
     * @param  Closure(array, array): array|null  $handler
     *
     * @internal
     */
    public function registerPermissionHandler(?Closure $handler): void
    {
        $this->permissionHandler = $handler;
    }

    /**
     * Handle a permission request.
     *
     * @internal
     */
    public function handlePermissionRequest(array $request): array
    {
        if ($this->permissionHandler === null) {
            return ['kind' => 'denied-no-approval-rule-and-could-not-request-from-user'];
        }

        try {
            return ($this->permissionHandler)($request, ['sessionId' => $this->sessionId]);
        } catch (\Throwable) {
            return ['kind' => 'denied-no-approval-rule-and-could-not-request-from-user'];
        }
    }

    /**
     * Get all messages from this session's history.
     *
     * @return array<SessionEvent>
     */
    public function getMessages(): array
    {
        $response = $this->client->request('session.getMessages', [
            'sessionId' => $this->sessionId,
        ]);

        $events = $response['events'] ?? [];

        return array_map(
            fn (array $event) => SessionEvent::fromArray($event),
            $events,
        );
    }

    /**
     * Destroy this session.
     */
    public function destroy(): void
    {
        $this->client->request('session.destroy', [
            'sessionId' => $this->sessionId,
        ]);

        $this->eventHandlers = [];
        $this->toolHandlers = [];
        $this->permissionHandler = null;
    }

    /**
     * Abort the currently processing message.
     */
    public function abort(): void
    {
        $this->client->request('session.abort', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
