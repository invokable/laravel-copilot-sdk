<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for the sub-agent stop hook.
 */
readonly class SubagentStopHookOutput implements Arrayable
{
    /**
     * @param  ?string  $decision  "block" to continue the sub-agent, or "allow" to accept its response
     * @param  ?string  $reason  Required by the runtime when blocking
     * @param  ?string  $modifiedResponse  Replacement response reported to the parent
     */
    public function __construct(
        public ?string $decision = null,
        public ?string $reason = null,
        public ?string $modifiedResponse = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            decision: $data['decision'] ?? null,
            reason: $data['reason'] ?? null,
            modifiedResponse: $data['modifiedResponse'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'decision' => $this->decision,
            'reason' => $this->reason,
            'modifiedResponse' => $this->modifiedResponse,
        ], fn ($value) => $value !== null);
    }
}
