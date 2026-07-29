<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for agent-stop hook.
 *
 * Return `decision: "block"` to keep the agent running: the `reason` is
 * enqueued as a follow-up user message so the agent continues working (for
 * example, to remediate findings surfaced by the hook). The runtime caps
 * consecutive blocks to prevent runaway loops. Returning nothing (or
 * omitting `decision`) lets the agent stop normally.
 */
readonly class AgentStopHookOutput implements Arrayable
{
    /**
     * @param  ?string  $decision  "block" to keep the agent running
     * @param  ?string  $reason  Reason enqueued as a follow-up user message when blocking
     */
    public function __construct(
        public ?string $decision = null,
        public ?string $reason = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            decision: $data['decision'] ?? null,
            reason: $data['reason'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'decision' => $this->decision,
            'reason' => $this->reason,
        ], fn ($value) => $value !== null);
    }
}
