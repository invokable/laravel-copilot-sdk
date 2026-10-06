<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Serialized preferences and available models used for initial model selection. */
readonly class AgentsCustomAgentInitialModelDecisionRequest implements Arrayable
{
    public function __construct(
        public string $agentModelsJson,
        public string $availableModelsJson,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            agentModelsJson: Arr::string($data, 'agentModelsJson'),
            availableModelsJson: Arr::string($data, 'availableModelsJson'),
        );
    }

    public function toArray(): array
    {
        return [
            'agentModelsJson' => $this->agentModelsJson,
            'availableModelsJson' => $this->availableModelsJson,
        ];
    }
}
