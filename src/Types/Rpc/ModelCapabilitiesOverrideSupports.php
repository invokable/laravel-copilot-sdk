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
     * @param  ?string  $adaptive_thinking  Resolved Anthropic adaptive-thinking capability for a model (experimental)
     */
    public function __construct(
        public ?bool $vision = null,
        public ?bool $reasoningEffort = null,
        public ?string $adaptive_thinking = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vision: $data['vision'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            adaptive_thinking: $data['adaptive_thinking'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'vision' => $this->vision,
            'reasoningEffort' => $this->reasoningEffort,
            'adaptive_thinking' => $this->adaptive_thinking,
        ], fn ($v) => $v !== null);
    }
}
