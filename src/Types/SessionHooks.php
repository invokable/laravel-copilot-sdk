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
    /**
     * @param  ?Closure  $onPreToolUse  Called before a tool is executed
     * @param  ?Closure  $onPostToolUse  Called after a tool is executed successfully
     * @param  ?Closure  $onPostToolUseFailure  Called after a tool execution fails
     * @param  ?Closure  $onUserPromptSubmitted  Called when the user submits a prompt
     * @param  ?Closure  $onSessionStart  Called when a session starts
     * @param  ?Closure  $onSessionEnd  Called when a session ends
     * @param  ?Closure  $onErrorOccurred  Called when an error occurs
     * @param  ?Closure  $onPreMcpToolCall  Called before an MCP tool call is dispatched
     */
    public function __construct(
        public ?Closure $onPreToolUse = null,
        public ?Closure $onPostToolUse = null,
        public ?Closure $onPostToolUseFailure = null,
        public ?Closure $onUserPromptSubmitted = null,
        public ?Closure $onSessionStart = null,
        public ?Closure $onSessionEnd = null,
        public ?Closure $onErrorOccurred = null,
        public ?Closure $onPreMcpToolCall = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            onPreToolUse: $data['onPreToolUse'] ?? null,
            onPostToolUse: $data['onPostToolUse'] ?? null,
            onPostToolUseFailure: $data['onPostToolUseFailure'] ?? null,
            onUserPromptSubmitted: $data['onUserPromptSubmitted'] ?? null,
            onSessionStart: $data['onSessionStart'] ?? null,
            onSessionEnd: $data['onSessionEnd'] ?? null,
            onErrorOccurred: $data['onErrorOccurred'] ?? null,
            onPreMcpToolCall: $data['onPreMcpToolCall'] ?? null,
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
            'onPostToolUseFailure' => $this->onPostToolUseFailure,
            'onUserPromptSubmitted' => $this->onUserPromptSubmitted,
            'onSessionStart' => $this->onSessionStart,
            'onSessionEnd' => $this->onSessionEnd,
            'onErrorOccurred' => $this->onErrorOccurred,
            'onPreMcpToolCall' => $this->onPreMcpToolCall,
        ], fn ($value) => $value !== null);
    }
}
