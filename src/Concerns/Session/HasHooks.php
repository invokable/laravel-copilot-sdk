<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Revolution\Copilot\Types\SessionHooks;
use Throwable;

/**
 * Manages session hooks registration and invocation.
 *
 * @internal
 */
trait HasHooks
{
    /**
     * Session hooks.
     */
    protected ?SessionHooks $hooks = null;

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
}
