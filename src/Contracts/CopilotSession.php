<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Closure;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Rpc\SessionRpc;
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
     * Destroy this session.
     */
    public function destroy(): void;
}
