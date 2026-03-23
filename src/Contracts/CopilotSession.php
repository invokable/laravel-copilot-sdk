<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Closure;
use Revolution\Copilot\Enums\LogLevel;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\InputOptions;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationResult;
use Revolution\Copilot\Types\SessionCapabilities;
use Revolution\Copilot\Types\SessionEvent;

/**
 * Represents a single conversation session with the Copilot CLI.
 */
interface CopilotSession
{
    /**
     * Get the session ID.
     */
    public function id(): string;

    /**
     * Typed session-scoped RPC methods.
     */
    public function rpc(): SessionRpc;

    /**
     * Get the host capabilities for this session.
     */
    public function capabilities(): SessionCapabilities;

    /**
     * Send a raw elicitation request to the CLI host.
     *
     * @throws \RuntimeException if the host does not support elicitation
     */
    public function elicitation(string $message, array $requestedSchema): SessionUiElicitationResult;

    /**
     * Show a confirmation dialog and return the user's boolean answer.
     * Returns `false` if the user declines or cancels.
     *
     * @throws \RuntimeException if the host does not support elicitation
     */
    public function confirm(string $message): bool;

    /**
     * Show a selection dialog with the given options.
     * Returns the selected value, or `null` if the user declines/cancels.
     *
     * @param  string[]  $options
     *
     * @throws \RuntimeException if the host does not support elicitation
     */
    public function select(string $message, array $options): ?string;

    /**
     * Show a text input dialog.
     * Returns the entered text, or `null` if the user declines/cancels.
     *
     * @throws \RuntimeException if the host does not support elicitation
     */
    public function input(string $message, InputOptions|array|null $options = null): ?string;

    /**
     * Switch the model for this session.
     *
     * @throws JsonRpcException
     */
    public function setModel(string $model, ReasoningEffort|string|null $reasoningEffort = null): void;

    /**
     * Log a message to the session timeline.
     *
     * @param  string  $message  Human-readable message text
     * @param  LogLevel|null  $level  Log severity. Defaults to info.
     * @param  bool|null  $ephemeral  When true, the message is not persisted to the session event log on disk.
     * @return string Event ID of the created log entry.
     *
     * @throws JsonRpcException
     */
    public function log(string $message, ?LogLevel $level = null, ?bool $ephemeral = null): string;

    /**
     * Send a message to this session.
     */
    public function send(string $prompt, ?array $attachments = null, ?string $mode = null): string;

    /**
     * Send a message and wait until the session becomes idle.
     */
    public function sendAndWait(string $prompt, ?array $attachments = null, ?string $mode = null, ?float $timeout = null): ?SessionEvent;

    /**
     * Subscribe to events from this session.
     *
     * When called with a single Closure argument, subscribes to all events.
     * When called with an event type and Closure, subscribes only to that specific event type.
     *
     * @param  string|SessionEventType|Closure|null  $type  Event type to filter, or handler for all events
     * @param  Closure(SessionEvent): void|null  $handler  Handler when type is specified
     * @return Closure(): void Unsubscribe function
     */
    public function on(string|SessionEventType|Closure|null $type = null, ?Closure $handler = null): Closure;

    /**
     * Unsubscribe from events.
     *
     * @param  Closure(SessionEvent): void  $handler
     */
    public function off(Closure $handler): void;

    /**
     * Send a message and yield events as a Generator until the session becomes idle.
     *
     * @return iterable<SessionEvent>
     */
    public function sendAndStream(string $prompt, ?array $attachments = null, ?string $mode = null, ?float $timeout = null): iterable;

    /**
     * Yield events as a Generator until the session becomes idle.
     *
     * @return iterable<SessionEvent>
     */
    public function stream(?float $timeout = null): iterable;

    /**
     * Get all messages from this session's history.
     *
     * @return array<SessionEvent>
     */
    public function getMessages(): array;

    /**
     * Disconnect this session and release all in-memory resources.
     * Session data on disk is preserved for later resumption.
     */
    public function disconnect(): void;

    /**
     * @deprecated Use disconnect() instead.
     */
    public function destroy(): void;
}
