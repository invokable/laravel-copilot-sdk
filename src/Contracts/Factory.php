<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;

interface Factory
{
    /**
     * Run a single prompt and return the response.
     *
     * @param  string  $prompt  The prompt/message to send
     * @param  array<array{type: string, path: string, displayName?: string}>|null  $attachments  File or directory attachments. type: "file" | "directory"
     * @param  ?string  $mode  Message delivery mode. "enqueue": Add to queue (default), "immediate": Send immediately
     */
    public function run(string $prompt, ?array $attachments = null, ?string $mode = null, SessionConfig|array $config = []): ?SessionEvent;

    /**
     * Start a session and execute a callback.
     *
     * @param  callable(CopilotSession): mixed  $callback
     * @param  ?string  $resume  Session ID to resume
     */
    public function start(callable $callback, SessionConfig|ResumeSessionConfig|array $config = [], ?string $resume = null): mixed;

    /**
     * Create a new session.
     */
    public function createSession(SessionConfig|array $config = []): CopilotSession;
}
