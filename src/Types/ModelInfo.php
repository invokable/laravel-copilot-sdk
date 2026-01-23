<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

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
    ) {}

    /**
     * Create from array.
     *
     * @param  array{id: string, name: string, capabilities: array, policy?: array, billing?: array}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            capabilities: ModelCapabilities::fromArray($data['capabilities']),
            policy: isset($data['policy']) ? ModelPolicy::fromArray($data['policy']) : null,
            billing: isset($data['billing']) ? ModelBilling::fromArray($data['billing']) : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'capabilities' => $this->capabilities->toArray(),
            'policy' => $this->policy?->toArray(),
            'billing' => $this->billing?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
