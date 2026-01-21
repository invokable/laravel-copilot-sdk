<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Types\SessionEvent;

interface Factory
{
    /**
     * Run a single prompt and return the response.
     */
    public function run(string $prompt, array $options = []): ?SessionEvent;

    /**
     * Start a session and execute a callback.
     *
     * @param  callable(CopilotSession): mixed  $callback
     */
    public function start(callable $callback, array $config = []): mixed;

    /**
     * Create a new session.
     */
    public function createSession(array $config = []): CopilotSession;
}
