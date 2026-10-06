<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class AgentsCustomAgentInitialModelDecisionResult implements Arrayable
{
    public function __construct(
        public ?string $targetModel = null,
        public ?string $reasoningEffort = null,
        public ?string $warning = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            targetModel: $data['targetModel'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            warning: $data['warning'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'targetModel' => $this->targetModel,
            'reasoningEffort' => $this->reasoningEffort,
            'warning' => $this->warning,
        ], static fn ($value) => $value !== null);
    }
}
