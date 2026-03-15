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
    /**
     * @param  string  $id  Model identifier (e.g., "claude-sonnet-4.5")
     * @param  string  $name  Display name
     * @param  ModelCapabilities  $capabilities  Model capabilities and limits
     * @param  ?ModelPolicy  $policy  Policy state
     * @param  ?ModelBilling  $billing  Billing information
     * @param  ?array  $supportedReasoningEfforts  Supported reasoning effort levels (only present if model supports reasoning effort)
     * @param  ReasoningEffort|string|null  $defaultReasoningEffort  Default reasoning effort level (only present if model supports reasoning effort)
     */
    public function __construct(
        public string $id,
        public string $name,
        public ModelCapabilities $capabilities,
        public ?ModelPolicy $policy = null,
        public ?ModelBilling $billing = null,
        public ?array $supportedReasoningEfforts = null,
        public ReasoningEffort|string|null $defaultReasoningEffort = null,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{id: string, name: string, capabilities: array, policy?: array, billing?: array, supportedReasoningEfforts?: array, defaultReasoningEffort?: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            capabilities: ModelCapabilities::fromArray($data['capabilities']),
            policy: isset($data['policy']) ? ModelPolicy::fromArray($data['policy']) : null,
            billing: isset($data['billing']) ? ModelBilling::fromArray($data['billing']) : null,
            supportedReasoningEfforts: $data['supportedReasoningEfforts'] ?? null,
            defaultReasoningEffort: $data['defaultReasoningEffort'] ?? null,
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
