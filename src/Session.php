<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Closure;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolt\EventLoop;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Events\Session\MessageSend;
use Revolution\Copilot\Events\Session\MessageSendAndWait;
use Revolution\Copilot\Events\Session\SessionEventReceived;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Exceptions\SessionErrorException;
use Revolution\Copilot\Exceptions\SessionTimeoutException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Support\PermissionRequestKind;
use Revolution\Copilot\Types\SessionEvent;
use Throwable;

/**
 * Represents a single conversation session with the Copilot CLI.
 */
class Session implements CopilotSession
{
    use Conditionable;
    use Macroable;

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

    /**
     * Wait state: idle flag.
     */
    protected bool $waitIdle = false;

    /**
     * Wait state: error message.
     */
    protected ?string $waitError = null;

    /**
     * Wait state: last assistant message.
     */
    protected ?SessionEvent $waitLastAssistantMessage = null;

    /**
     * Wait state: event handler.
     */
    protected ?Closure $waitHandler = null;

    public function __construct(
        public readonly string $sessionId,
        protected JsonRpcClient $client,
        public readonly ?string $workspacePath = null,
    ) {
        //
    }

    /**
     * Get the session ID.
     */
    public function id(): string
    {
        return $this->sessionId;
    }

    /**
     * Path to the session workspace directory when infinite sessions are enabled.
     * Contains checkpoints/, plan.md, and files/ subdirectories.
     * Null if infinite sessions are disabled.
     */
    public function workspacePath(): ?string
    {
        return $this->workspacePath;
    }

    /**
     * Send a message to this session.
     *
     * @param  string  $prompt  The prompt/message to send
     * @param  array<array{type: string, path: string, displayName?: string}>|null  $attachments  File or directory attachments. type: "file" | "directory"
     * @param  ?string  $mode  Message delivery mode. "enqueue": Add to queue (default), "immediate": Send immediately
     *
     * @throws JsonRpcException
     */
    public function send(string $prompt, ?array $attachments = null, ?string $mode = null): string
    {
        $response = $this->client->request('session.send', [
            'sessionId' => $this->sessionId,
            'prompt' => $prompt,
            'attachments' => $attachments,
            'mode' => $mode,
        ]);

        MessageSend::dispatch($this->sessionId, $response['messageId'] ?? '', $prompt, $attachments, $mode);

        return $response['messageId'] ?? '';
    }

    /**
     * Send a message and wait until the session becomes idle.
     *
     * @param  string  $prompt  The prompt/message to send
     * @param  array<array{type: string, path: string, displayName?: string}>|null  $attachments  File or directory attachments. type: "file" | "directory"
     * @param  ?string  $mode  Message delivery mode. "enqueue": Add to queue (default), "immediate": Send immediately
     * @param  float  $timeout  Maximum time to wait for idle state, in seconds
     */
    public function sendAndWait(string $prompt, ?array $attachments = null, ?string $mode = null, ?float $timeout = null): ?SessionEvent
    {
        $timeout = $timeout ?? config('copilot.timeout', 60.0);

        $this->prepareWait();

        try {
            $this->send($prompt, $attachments, $mode);
            $this->wait($timeout);

            MessageSendAndWait::dispatch($this->sessionId, $this->waitLastAssistantMessage, $prompt, $attachments, $mode);

            return $this->waitLastAssistantMessage;
        } catch (JsonRpcException $e) {
            $event = new SessionEvent(
                id: '',
                timestamp: now()->toDateTimeString(),
                parentId: $this->sessionId,
                type: SessionEventType::SESSION_ERROR,
                data: [
                    'message' => $e->getMessage(),
                ],
                exception: $e,
            );
            $this->dispatchEvent($event);

            return $event;
        } finally {
            $this->cleanupWait();
        }
    }

    /**
     * Prepare wait state and register event handler.
     */
    protected function prepareWait(): void
    {
        $this->waitIdle = false;
        $this->waitError = null;
        $this->waitLastAssistantMessage = null;

        $this->waitHandler = function (SessionEvent $event): void {
            if ($event->isAssistantMessage()) {
                $this->waitLastAssistantMessage = $event;
            } elseif ($event->isIdle()) {
                $this->waitIdle = true;
            } elseif ($event->failed()) {
                $this->waitError = $event->errorMessage() ?? 'Unknown error';
            }
        };

        $this->on($this->waitHandler);
    }

    /**
     * Wait until the session becomes idle or an error occurs.
     *
     * @param  float  $timeout  Maximum time to wait, in seconds
     */
    public function wait(float $timeout = 60.0): void
    {
        $suspension = EventLoop::getSuspension();

        $timeoutId = EventLoop::delay($timeout, function () use ($suspension): void {
            $suspension->resume();
        });

        $checkId = EventLoop::repeat(0.01, function () use ($suspension, &$checkId): void {
            $this->client->processMessages(0.1);

            if ($this->waitIdle || $this->waitError !== null) {
                $suspension->resume();
            }
        });

        $suspension->suspend();

        EventLoop::cancel($timeoutId);
        EventLoop::cancel($checkId);

        if ($this->waitError !== null) {
            $event = new SessionEvent(
                id: '',
                timestamp: now()->toDateTimeString(),
                parentId: $this->sessionId,
                type: SessionEventType::SESSION_ERROR,
                data: [
                    'message' => $this->waitError,
                ],
                exception: new SessionErrorException($this->waitError),
            );

            $this->dispatchEvent($event);
        }

        if (! $this->waitIdle) {
            $event = new SessionEvent(
                id: '',
                timestamp: now()->toDateTimeString(),
                parentId: $this->sessionId,
                type: SessionEventType::SESSION_ERROR,
                data: [
                    'message' => 'Session wait timed out after '.$timeout.' seconds',
                ],
                exception: new SessionTimeoutException($timeout),
            );

            $this->dispatchEvent($event);
        }
    }

    /**
     * Cleanup wait state and unregister event handler.
     */
    protected function cleanupWait(): void
    {
        if ($this->waitHandler !== null) {
            $this->off($this->waitHandler);
            $this->waitHandler = null;
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
        SessionEventReceived::dispatch($this->sessionId, $event);

        foreach ($this->eventHandlers as $handler) {
            try {
                $handler($event);
            } catch (Throwable) {
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
            return PermissionRequestKind::deniedNoApprovalRuleAndCouldNotRequestFromUser();
        }

        try {
            return ($this->permissionHandler)($request, ['sessionId' => $this->sessionId]);
        } catch (Throwable) {
            return PermissionRequestKind::deniedNoApprovalRuleAndCouldNotRequestFromUser();
        }
    }

    /**
     * Get all messages from this session's history.
     *
     * @return array<SessionEvent>
     *
     * @throws JsonRpcException
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
     *
     * @throws JsonRpcException
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
     *
     * @throws JsonRpcException
     */
    public function abort(): void
    {
        $this->client->request('session.abort', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
