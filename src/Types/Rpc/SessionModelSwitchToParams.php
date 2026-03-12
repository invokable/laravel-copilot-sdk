<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;

/**
 * Parameters for switching session model.
 */
readonly class SessionModelSwitchToParams implements Arrayable
{
    public function __construct(
        public string $modelId,
        /**
         * Reasoning effort level to use for the model.
         * Accepts either ReasoningEffort enum or string value.
         */
        public ReasoningEffort|string|null $reasoningEffort = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'],
            reasoningEffort: $data['reasoningEffort'] ?? null,
        );
    }

    public function toArray(): array
    {
        $reasoningEffort = $this->reasoningEffort instanceof ReasoningEffort
            ? $this->reasoningEffort->value
            : $this->reasoningEffort;

        return array_filter([
            'modelId' => $this->modelId,
            'reasoningEffort' => $reasoningEffort,
        ], fn ($v) => $v !== null);
    }
}
