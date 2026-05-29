<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;

/**
 * Parameters for switching session model.
 */
readonly class ModelSwitchToRequest implements Arrayable
{
    /**
     * @param  string  $modelId  The model ID to switch to.
     * @param  ReasoningEffort|string|null  $reasoningEffort  Reasoning effort level to use for the model.
     *                                                        Accepts either ReasoningEffort enum or string value.
     * @param  string|null  $reasoningSummary  Reasoning summary mode ("auto", "concise", "detailed", "none").
     * @param  ModelCapabilitiesOverride|array|null  $modelCapabilities  Override individual model capabilities resolved by the runtime.
     * @param  string|null  $contextTier  Explicit context tier ("default" or "long_context"). Null clears any previous choice.
     */
    public function __construct(
        public string $modelId,
        public ReasoningEffort|string|null $reasoningEffort = null,
        public ?string $reasoningSummary = null,
        public ModelCapabilitiesOverride|array|null $modelCapabilities = null,
        public ?string $contextTier = null,
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
            reasoningSummary: $data['reasoningSummary'] ?? null,
            modelCapabilities: $modelCapabilities,
            contextTier: $data['contextTier'] ?? null,
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
            'reasoningSummary' => $this->reasoningSummary,
            'modelCapabilities' => $modelCapabilities,
            'contextTier' => $this->contextTier,
        ], fn ($v) => $v !== null);
    }
}
