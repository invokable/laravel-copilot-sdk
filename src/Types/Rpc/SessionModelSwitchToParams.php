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
    /**
     * @param  string  $modelId  The model ID to switch to
     * @param  ReasoningEffort|string|null  $reasoningEffort  Reasoning effort level to use for the model.
     *                                                        Accepts either ReasoningEffort enum or string value.
     * @param  ModelCapabilitiesOverride|array|null  $modelCapabilities  Override individual model capabilities resolved by the runtime.
     */
    public function __construct(
        public string $modelId,
        public ReasoningEffort|string|null $reasoningEffort = null,
        public ModelCapabilitiesOverride|array|null $modelCapabilities = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $modelCapabilities = isset($data['modelCapabilities'])
            ? ($data['modelCapabilities'] instanceof ModelCapabilitiesOverride
                ? $data['modelCapabilities']
                : ModelCapabilitiesOverride::fromArray($data['modelCapabilities']))
            : null;

        return new self(
            modelId: $data['modelId'],
            reasoningEffort: $data['reasoningEffort'] ?? null,
            modelCapabilities: $modelCapabilities,
        );
    }

    public function toArray(): array
    {
        $reasoningEffort = $this->reasoningEffort instanceof ReasoningEffort
            ? $this->reasoningEffort->value
            : $this->reasoningEffort;

        $modelCapabilities = $this->modelCapabilities instanceof ModelCapabilitiesOverride
            ? $this->modelCapabilities->toArray()
            : $this->modelCapabilities;

        return array_filter([
            'modelId' => $this->modelId,
            'reasoningEffort' => $reasoningEffort,
            'modelCapabilities' => $modelCapabilities,
        ], fn ($v) => $v !== null);
    }
}
