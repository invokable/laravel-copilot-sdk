<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Closure;
use Revolution\Copilot\Types\SessionEvent;
use RuntimeException;

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
     * Send a message to this session.
     */
    public function send(string $prompt, ?array $attachments = null, ?string $mode = null): string;

    /**
     * Send a message and wait until the session becomes idle.
     *
     * @throws RuntimeException
     */
    public function sendAndWait(string $prompt, ?array $attachments = null, ?string $mode = null, float $timeout = 60.0): ?SessionEvent;

    /**
     * Subscribe to events from this session.
     *
     * @param  Closure(SessionEvent): void  $handler
     * @return Closure(): void Unsubscribe function
     */
    public function on(Closure $handler): Closure;

    /**
     * Unsubscribe from events.
     *
     * @param  Closure(SessionEvent): void  $handler
     */
    public function off(Closure $handler): void;

    /**
     * Get all messages from this session's history.
     *
     * @return array<SessionEvent>
     */
    public function getMessages(): array;
}
