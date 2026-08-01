<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for compacting session history.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryCompactRequest implements Arrayable
{
    /**
     * @param  ?string  $customInstructions  Optional user-provided instructions to focus the compaction summary.
     * @param  ?string  $trigger  What initiated this compaction request: `manual` or `model_switch`.
     * @param  ?int  $tokenLimit  Context window token limit this compaction is targeting.
     */
    public function __construct(
        public ?string $customInstructions = null,
        public ?string $trigger = null,
        public ?int $tokenLimit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customInstructions: $data['customInstructions'] ?? null,
            trigger: $data['trigger'] ?? null,
            tokenLimit: $data['tokenLimit'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'customInstructions' => $this->customInstructions,
            'trigger' => $this->trigger,
            'tokenLimit' => $this->tokenLimit,
        ], fn ($v) => $v !== null);
    }
}
