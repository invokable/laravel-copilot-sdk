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
        public ?ReasoningEffort $reasoningEffort = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'],
            reasoningEffort: isset($data['reasoningEffort']) ? ReasoningEffort::from($data['reasoningEffort']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modelId' => $this->modelId,
            'reasoningEffort' => $this->reasoningEffort?->value,
        ], fn ($v) => $v !== null);
    }
}
