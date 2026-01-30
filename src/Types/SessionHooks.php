<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration for session hooks.
 */
readonly class SessionHooks implements Arrayable
{
    public function __construct(
        /**
         * Called before a tool is executed.
         */
        public ?Closure $onPreToolUse = null,
        /**
         * Called after a tool is executed.
         */
        public ?Closure $onPostToolUse = null,
        /**
         * Called when the user submits a prompt.
         */
        public ?Closure $onUserPromptSubmitted = null,
        /**
         * Called when a session starts.
         */
        public ?Closure $onSessionStart = null,
        /**
         * Called when a session ends.
         */
        public ?Closure $onSessionEnd = null,
        /**
         * Called when an error occurs.
         */
        public ?Closure $onErrorOccurred = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            onPreToolUse: $data['onPreToolUse'] ?? null,
            onPostToolUse: $data['onPostToolUse'] ?? null,
            onUserPromptSubmitted: $data['onUserPromptSubmitted'] ?? null,
            onSessionStart: $data['onSessionStart'] ?? null,
            onSessionEnd: $data['onSessionEnd'] ?? null,
            onErrorOccurred: $data['onErrorOccurred'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'onPreToolUse' => $this->onPreToolUse,
            'onPostToolUse' => $this->onPostToolUse,
            'onUserPromptSubmitted' => $this->onUserPromptSubmitted,
            'onSessionStart' => $this->onSessionStart,
            'onSessionEnd' => $this->onSessionEnd,
            'onErrorOccurred' => $this->onErrorOccurred,
        ], fn ($value) => $value !== null);
    }
}
