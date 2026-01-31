<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;

/**
 * Information about an available model.
 */
readonly class ModelInfo implements Arrayable
{
    public function __construct(
        /** Model identifier (e.g., "claude-sonnet-4.5") */
        public string $id,
        /** Display name */
        public string $name,
        /** Model capabilities and limits */
        public ModelCapabilities $capabilities,
        /** Policy state */
        public ?ModelPolicy $policy = null,
        /** Billing information */
        public ?ModelBilling $billing = null,
        /** Supported reasoning effort levels (only present if model supports reasoning effort) */
        public ?array $supportedReasoningEfforts = null,
        /** Default reasoning effort level (only present if model supports reasoning effort) */
        public ReasoningEffort|string|null $defaultReasoningEffort = null,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{id: string, name: string, capabilities: array, policy?: array, billing?: array, supportedReasoningEfforts?: array, defaultReasoningEffort?: string}  $data
     */
    public static function fromArray(array $data): self
    {
        $defaultReasoningEffort = null;
        if (isset($data['defaultReasoningEffort'])) {
            $defaultReasoningEffort = is_string($data['defaultReasoningEffort'])
                ? $data['defaultReasoningEffort']
                : $data['defaultReasoningEffort'];
        }

        return new self(
            id: $data['id'],
            name: $data['name'],
            capabilities: ModelCapabilities::fromArray($data['capabilities']),
            policy: isset($data['policy']) ? ModelPolicy::fromArray($data['policy']) : null,
            billing: isset($data['billing']) ? ModelBilling::fromArray($data['billing']) : null,
            supportedReasoningEfforts: $data['supportedReasoningEfforts'] ?? null,
            defaultReasoningEffort: $defaultReasoningEffort,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $defaultReasoningEffort = $this->defaultReasoningEffort instanceof ReasoningEffort
            ? $this->defaultReasoningEffort->value
            : $this->defaultReasoningEffort;

        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'capabilities' => $this->capabilities->toArray(),
            'policy' => $this->policy?->toArray(),
            'billing' => $this->billing?->toArray(),
            'supportedReasoningEfforts' => $this->supportedReasoningEfforts,
            'defaultReasoningEffort' => $defaultReasoningEffort,
        ], fn ($v) => $v !== null);
    }
}
