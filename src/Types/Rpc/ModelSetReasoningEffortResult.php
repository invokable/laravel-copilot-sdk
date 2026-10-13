<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ReasoningEffort;

/**
 * Result of updating the session's reasoning effort.
 *
 * @experimental
 */
readonly class ModelSetReasoningEffortResult implements Arrayable
{
    /**
     * @param  ReasoningEffort|string  $reasoningEffort  Reasoning effort recorded on the session.
     */
    public function __construct(
        public ReasoningEffort|string $reasoningEffort,
    ) {}

    public static function fromArray(array $data): self
    {
        $reasoningEffort = Arr::string($data, 'reasoningEffort');

        return new self(
            reasoningEffort: ReasoningEffort::tryFrom($reasoningEffort) ?? $reasoningEffort,
        );
    }

    public function toArray(): array
    {
        return [
            'reasoningEffort' => $this->reasoningEffort instanceof ReasoningEffort
                ? $this->reasoningEffort->value
                : $this->reasoningEffort,
        ];
    }
}
