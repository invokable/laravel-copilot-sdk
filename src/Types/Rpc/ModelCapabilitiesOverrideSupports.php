<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Feature flags indicating what the model supports (override).
 */
readonly class ModelCapabilitiesOverrideSupports implements Arrayable
{
    /**
     * @param  ?bool  $vision  Whether this model supports vision/image input
     * @param  ?bool  $reasoningEffort  Whether this model supports reasoning effort configuration
     */
    public function __construct(
        public ?bool $vision = null,
        public ?bool $reasoningEffort = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vision: $data['vision'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'vision' => $this->vision,
            'reasoningEffort' => $this->reasoningEffort,
        ], fn ($v) => $v !== null);
    }
}
