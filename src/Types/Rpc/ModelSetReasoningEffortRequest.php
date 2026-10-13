<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ReasoningEffort;

/**
 * Parameters for updating reasoning effort without changing the selected model.
 *
 * @experimental
 */
readonly class ModelSetReasoningEffortRequest implements Arrayable
{
    /**
     * @param  ReasoningEffort|string  $reasoningEffort  Reasoning effort to apply to the current model.
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
