<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Closure;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Revolt\EventLoop;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\LogLevel;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Events\Session\MessageSend;
use Revolution\Copilot\Events\Session\MessageSendAndWait;
use Revolution\Copilot\Events\Session\SessionEventReceived;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Exceptions\SessionErrorException;
use Revolution\Copilot\Exceptions\SessionTimeoutException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Support\PermissionRequestResultKind;
use Revolution\Copilot\Types\Rpc\SessionLogParams;
use Revolution\Copilot\Types\Rpc\SessionModelSwitchToParams;
use Revolution\Copilot\Types\Rpc\SessionPermissionsHandlePendingPermissionRequestParams;
use Revolution\Copilot\Types\Rpc\SessionToolsHandlePendingToolCallParams;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\SessionHooks;
use Revolution\Copilot\Types\ToolResultObject;
use Revolution\Copilot\Types\UserInputRequest;
use Revolution\Copilot\Types\UserInputResponse;
use Throwable;

/**
 * Represents a single conversation session with the Copilot CLI.
 */
class Session implements CopilotSession
{
    use Conditionable;
    use Macroable;

    /**
     * Event handlers (wildcard).
     *
     * @var array<Closure(SessionEvent): void>
     */
    protected array $eventHandlers = [];

    /**
     * Typed event handlers.
     *
     * @var array<string, array<Closure(SessionEvent): void>>
     */
    protected array $typedEventHandlers = [];

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
     * User input handler.
     *
     * @var Closure(UserInputRequest, array): UserInputResponse|null
     */
    protected ?Closure $userInputHandler = null;

    /**
     * Session hooks.
     */
    protected ?SessionHooks $hooks = null;

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
     * Typed session-scoped RPC methods.
     */
    public function rpc(): SessionRpc
    {
        return new SessionRpc($this->client, $this->sessionId);
    }

    /**
     * Send a message to this session.
     *
     * @param  string  $prompt  The prompt/message to send
     * @param  array<array{type: string, path: string, displayName?: string}>|null  $attachments  File or directory attachments. type: "file" | "directory"
     * @param  ?string  $mode  Message delivery mode. "enqueue": Queue for processing after current turn (default). "immediate": Inject into current turn (steering). Omit for normal use.
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
     * @param  ?string  $mode  Message delivery mode. "enqueue": Queue for processing after current turn (default). "immediate": Inject into current turn (steering). Omit for normal use.
     * @param  ?float  $timeout  Maximum time to wait for idle state, in seconds
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

        $checkId = EventLoop::repeat(0.01, function () use ($suspension): void {
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
     * When called with a single Closure argument, subscribes to all events.
     * When called with an event type and Closure, subscribes only to that specific event type.
     *
     * @param  string|SessionEventType|Closure|null  $type  Event type to filter, or handler for all events
     * @param  Closure(SessionEvent): void|null  $handler  Handler when type is specified
     * @return Closure(): void Unsubscribe function
     *
     * @example Typed event subscription
     * ```php
     * $session->on(SessionEventType::ASSISTANT_MESSAGE, function (SessionEvent $event) {
     *     echo $event->content();
     * });
     *
     * $session->on('assistant.message_delta', function (SessionEvent $event) {
     *     echo $event->data['deltaContent'] ?? '';
     * });
     * ```
     * @example Wildcard subscription (all events)
     * ```php
     * $session->on(function (SessionEvent $event) {
     *     echo $event->type();
     * });
     *
     * $session->on(handler: function (SessionEvent $event) {
     *      echo $event->type();
     *  });
     * ```
     */
    public function on(string|SessionEventType|Closure|null $type = null, ?Closure $handler = null): Closure
    {
        // Overload 1: on(type, handler) - typed event subscription
        if ($type !== null && $handler !== null) {
            $eventType = $type instanceof SessionEventType ? $type->value : (string) $type;

            $this->typedEventHandlers[$eventType] ??= [];
            $this->typedEventHandlers[$eventType][] = $handler;

            return function () use ($eventType, $handler): void {
                $this->typedEventHandlers[$eventType] = array_filter(
                    $this->typedEventHandlers[$eventType] ?? [],
                    fn ($h) => $h !== $handler,
                );
            };
        }

        // Overload 2: on(handler) - wildcard subscription
        if ($type instanceof Closure) {
            $this->eventHandlers[] = $type;

            return fn () => $this->off($type);
        }

        // Overload 3: on(handler: handler) - Named Parameters
        if ($type === null && $handler instanceof Closure) {
            $this->eventHandlers[] = $handler;

            return fn () => $this->off($handler);
        }

        // Invalid call: type without handler
        throw new InvalidArgumentException('Handler must be provided when specifying an event type');
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
     * Send a message and yield events as a Generator until the session becomes idle.
     *
     * @param  string  $prompt  The prompt/message to send
     * @param  array<array{type: string, path: string, displayName?: string}>|null  $attachments  File or directory attachments
     * @param  ?string  $mode  Message delivery mode. "enqueue": Queue for processing after current turn (default). "immediate": Inject into current turn (steering). Omit for normal use.
     * @param  float|null  $timeout  Maximum time to wait for idle state, in seconds
     * @return iterable<SessionEvent>
     */
    public function sendAndStream(string $prompt, ?array $attachments = null, ?string $mode = null, ?float $timeout = null): iterable
    {
        $this->send($prompt, $attachments, $mode);

        yield from $this->stream($timeout);
    }

    /**
     * Yield events as a Generator until the session becomes idle.
     *
     * @param  float|null  $timeout  Maximum time to wait for idle state, in seconds
     * @return iterable<SessionEvent>
     */
    public function stream(?float $timeout = null): iterable
    {
        $timeout = $timeout ?? config('copilot.timeout', 60.0);
        $queue = new \SplQueue;
        $idle = false;
        $error = null;

        $handler = function (SessionEvent $event) use ($queue, &$idle, &$error): void {
            $queue->enqueue($event);

            if ($event->isIdle()) {
                $idle = true;
            } elseif ($event->failed()) {
                $error = $event->errorMessage() ?? 'Unknown error';
            }
        };

        $this->on($handler);

        $startTime = microtime(true);

        try {
            while (! $idle && $error === null) {
                // Check timeout
                if ((microtime(true) - $startTime) > $timeout) {
                    $event = new SessionEvent(
                        id: '',
                        timestamp: now()->toDateTimeString(),
                        parentId: $this->sessionId,
                        type: SessionEventType::SESSION_ERROR,
                        data: [
                            'message' => 'Session stream timed out after '.$timeout.' seconds',
                        ],
                        exception: new SessionTimeoutException($timeout),
                    );
                    yield $event;

                    return;
                }

                // Yield all queued events
                while (! $queue->isEmpty()) {
                    yield $queue->dequeue();
                }

                // Small delay to prevent busy loop, allow event loop to process
                $suspension = EventLoop::getSuspension();
                EventLoop::delay(0.001, fn () => $suspension->resume());
                $suspension->suspend();
            }

            // Yield remaining events
            while (! $queue->isEmpty()) {
                yield $queue->dequeue();
            }

            // Yield error event if any
            if ($error !== null) {
                $event = new SessionEvent(
                    id: '',
                    timestamp: now()->toDateTimeString(),
                    parentId: $this->sessionId,
                    type: SessionEventType::SESSION_ERROR,
                    data: [
                        'message' => $error,
                    ],
                    exception: new SessionErrorException($error),
                );
                yield $event;
            }
        } finally {
            $this->off($handler);
        }
    }

    /**
     * Dispatch an event to all registered handlers.
     * Also handles broadcast request events internally (external tool calls, permissions).
     *
     * @internal
     */
    public function dispatchEvent(SessionEvent $event): void
    {
        SessionEventReceived::dispatch($this->sessionId, $event);

        // Handle broadcast request events internally (protocol v3+) before dispatching to user handlers.
        // Fire-and-forget: responses are sent asynchronously via RPC using a new Fiber.
        $this->handleBroadcastEvent($event);

        // Dispatch to typed handlers for this specific event type
        $eventType = $event->type();
        if (isset($this->typedEventHandlers[$eventType])) {
            foreach ($this->typedEventHandlers[$eventType] as $handler) {
                try {
                    $handler($event);
                } catch (Throwable) {
                    // Ignore handler errors
                }
            }
        }

        // Dispatch to wildcard handlers
        foreach ($this->eventHandlers as $handler) {
            try {
                $handler($event);
            } catch (Throwable) {
                // Ignore handler errors
            }
        }
    }

    /**
     * Handle broadcast request events (protocol v3+) by executing local handlers
     * and responding via RPC. Uses a new Fiber to allow async RPC calls (fire-and-forget).
     *
     * @internal
     */
    protected function handleBroadcastEvent(SessionEvent $event): void
    {
        if ($event->is(SessionEventType::EXTERNAL_TOOL_REQUESTED)) {
            $requestId = $event->data['requestId'] ?? null;
            $toolName = $event->data['toolName'] ?? null;
            $arguments = $event->data['arguments'] ?? [];
            $toolCallId = $event->data['toolCallId'] ?? null;
            $traceparent = $event->data['traceparent'] ?? null;
            $tracestate = $event->data['tracestate'] ?? null;

            if ($requestId === null || $toolName === null) {
                return;
            }

            $handler = $this->getToolHandler($toolName);

            if ($handler === null) {
                return; // This client doesn't handle this tool; another client will.
            }

            $this->executeToolAndRespond($requestId, $toolName, $toolCallId, $arguments, $handler, $traceparent, $tracestate);
        } elseif ($event->is(SessionEventType::PERMISSION_REQUESTED)) {
            $requestId = $event->data['requestId'] ?? null;
            $permissionRequest = $event->data['permissionRequest'] ?? [];

            if ($requestId === null || $this->permissionHandler === null) {
                return;
            }

            $this->executePermissionAndRespond($requestId, $permissionRequest);
        }
    }

    /**
     * Execute a tool handler and send the result back via RPC.
     * Runs in a new Fiber to allow async RPC calls without blocking the event loop.
     *
     * @internal
     */
    protected function executeToolAndRespond(string $requestId, string $toolName, ?string $toolCallId, mixed $arguments, Closure $handler, ?string $traceparent = null, ?string $tracestate = null): void
    {
        $fiber = new \Fiber(function () use ($requestId, $toolName, $toolCallId, $arguments, $handler, $traceparent, $tracestate): void {
            try {
                $invocation = [
                    'sessionId' => $this->sessionId,
                    'toolCallId' => $toolCallId,
                    'toolName' => $toolName,
                    'arguments' => $arguments,
                ];

                if ($traceparent !== null) {
                    $invocation['traceparent'] = $traceparent;
                }
                if ($tracestate !== null) {
                    $invocation['tracestate'] = $tracestate;
                }

                /** @var ToolResultObject|array|string|mixed $rawResult */
                $rawResult = $handler($arguments, $invocation);

                if ($rawResult instanceof ToolResultObject) {
                    $result = $rawResult->toArray();
                } elseif (is_string($rawResult) || is_array($rawResult)) {
                    $result = $rawResult;
                } else {
                    $result = $rawResult !== null ? (string) $rawResult : '';
                }

                // Send failure via error param for consistent server-side formatting
                if (is_array($result) && ($result['resultType'] ?? null) === 'failure' && isset($result['error'])) {
                    $this->rpc()->tools()->handlePendingToolCall(
                        new SessionToolsHandlePendingToolCallParams(
                            requestId: $requestId,
                            error: $result['error'],
                        )
                    );
                } else {
                    $this->rpc()->tools()->handlePendingToolCall(
                        new SessionToolsHandlePendingToolCallParams(
                            requestId: $requestId,
                            result: $result,
                        )
                    );
                }
            } catch (Throwable $e) {
                try {
                    $this->rpc()->tools()->handlePendingToolCall(
                        new SessionToolsHandlePendingToolCallParams(
                            requestId: $requestId,
                            error: $e->getMessage(),
                        )
                    );
                } catch (Throwable) {
                    // Connection lost or RPC error — nothing we can do
                }
            }
        });

        $fiber->start();
    }

    /**
     * Execute the permission handler and send the result back via RPC.
     * Runs in a new Fiber to allow async RPC calls without blocking the event loop.
     *
     * @internal
     */
    protected function executePermissionAndRespond(string $requestId, array $permissionRequest): void
    {
        $fiber = new \Fiber(function () use ($requestId, $permissionRequest): void {
            try {
                $result = ($this->permissionHandler)($permissionRequest, ['sessionId' => $this->sessionId]);

                if (($result['kind'] ?? null) === PermissionRequestResultKind::NO_RESULT) {
                    return;
                }

                $this->rpc()->permissions()->handlePendingPermissionRequest(
                    new SessionPermissionsHandlePendingPermissionRequestParams(
                        requestId: $requestId,
                        result: $result,
                    )
                );
            } catch (Throwable) {
                try {
                    $this->rpc()->permissions()->handlePendingPermissionRequest(
                        new SessionPermissionsHandlePendingPermissionRequestParams(
                            requestId: $requestId,
                            result: PermissionRequestResultKind::deniedNoApprovalRuleAndCouldNotRequestFromUser(),
                        )
                    );
                } catch (Throwable) {
                    // Connection lost or RPC error — nothing we can do
                }
            }
        });

        $fiber->start();
    }

    /**
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
            return PermissionRequestResultKind::deniedNoApprovalRuleAndCouldNotRequestFromUser();
        }

        try {
            return ($this->permissionHandler)($request, ['sessionId' => $this->sessionId]);
        } catch (Throwable) {
            return PermissionRequestResultKind::deniedNoApprovalRuleAndCouldNotRequestFromUser();
        }
    }

    /**
     * Register a user input handler.
     *
     * @param  Closure(UserInputRequest, array): UserInputResponse|null  $handler
     *
     * @internal
     */
    public function registerUserInputHandler(?Closure $handler): void
    {
        $this->userInputHandler = $handler;
    }

    /**
     * Handle a user input request.
     *
     * @throws \RuntimeException
     *
     * @internal
     */
    public function handleUserInputRequest(UserInputRequest $request): UserInputResponse
    {
        if ($this->userInputHandler === null) {
            throw new \RuntimeException('User input requested but no handler registered');
        }

        /** @var UserInputResponse|array $result */
        $result = ($this->userInputHandler)($request, ['sessionId' => $this->sessionId]);

        return $result instanceof UserInputResponse
            ? $result
            : UserInputResponse::fromArray($result);
    }

    /**
     * Register session hooks.
     *
     * @internal
     */
    public function registerHooks(SessionHooks|array|null $hooks): void
    {
        $this->hooks = $hooks instanceof SessionHooks
            ? $hooks
            : ($hooks !== null ? SessionHooks::fromArray($hooks) : null);
    }

    /**
     * Handle a hooks invocation.
     *
     * @internal
     */
    public function handleHooksInvoke(string $hookType, mixed $input): mixed
    {
        if ($this->hooks === null) {
            return null;
        }

        $handlerMap = [
            'preToolUse' => $this->hooks->onPreToolUse,
            'postToolUse' => $this->hooks->onPostToolUse,
            'userPromptSubmitted' => $this->hooks->onUserPromptSubmitted,
            'sessionStart' => $this->hooks->onSessionStart,
            'sessionEnd' => $this->hooks->onSessionEnd,
            'errorOccurred' => $this->hooks->onErrorOccurred,
        ];

        $handler = $handlerMap[$hookType] ?? null;

        if ($handler === null) {
            return null;
        }

        try {
            return $handler($input, ['sessionId' => $this->sessionId]);
        } catch (Throwable) {
            return null;
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
     * Disconnect this session and release all in-memory resources (event handlers,
     * tool handlers, permission handlers).
     *
     * Session state on disk (conversation history, planning state, artifacts) is
     * preserved, so the conversation can be resumed later via resumeSession().
     * To permanently remove all session data including files on disk, use
     * deleteSession() instead.
     *
     * @throws JsonRpcException
     */
    public function disconnect(): void
    {
        $this->client->request('session.destroy', [
            'sessionId' => $this->sessionId,
        ]);

        $this->eventHandlers = [];
        $this->typedEventHandlers = [];
        $this->toolHandlers = [];
        $this->permissionHandler = null;
        $this->userInputHandler = null;
        $this->hooks = null;
    }

    /**
     * @deprecated Use disconnect() instead. This method will be removed in a future release.
     *
     * Disconnect this session and release all in-memory resources.
     * Session data on disk is preserved for later resumption.
     *
     * @throws JsonRpcException
     */
    #[\Deprecated(message: 'Use disconnect() instead. This method will be removed in a future release.', since: '0.2.28')]
    public function destroy(): void
    {
        $this->disconnect();
    }

    /**
     * Switch the model for this session.
     *
     * @throws JsonRpcException
     */
    public function setModel(string $model, ReasoningEffort|string|null $reasoningEffort = null): void
    {
        $this->rpc()->model()->switchTo(new SessionModelSwitchToParams(modelId: $model, reasoningEffort: $reasoningEffort));
    }

    /**
     * Log a message to the session timeline.
     * The message appears in the session event stream and is visible to SDK consumers
     * and (for non-ephemeral messages) persisted to the session event log on disk.
     *
     * @param  string  $message  Human-readable message text
     * @param  LogLevel|null  $level  Log severity. Defaults to info.
     * @param  bool|null  $ephemeral  When true, the message is not persisted to the session event log on disk.
     * @return string Event ID of the created log entry.
     *
     * @throws JsonRpcException
     */
    public function log(string $message, ?LogLevel $level = null, ?bool $ephemeral = null): string
    {
        $result = $this->rpc()->log()->log(new SessionLogParams(
            message: $message,
            level: $level,
            ephemeral: $ephemeral,
        ));

        return $result->eventId;
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
