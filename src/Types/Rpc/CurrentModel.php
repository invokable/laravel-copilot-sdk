<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting current model for a session.
 */
readonly class CurrentModel implements Arrayable
{
    /**
     * @param  ?string  $modelId  The currently active model ID, or null if using the default model.
     * @param  ?string  $reasoningEffort  Reasoning effort level currently applied to the active model.
     * @param  ?string  $contextTier  Context tier currently pinned for the session ("default" or "long_context").
     */
    public function __construct(
        public ?string $modelId = null,
        public ?string $reasoningEffort = null,
        public ?string $contextTier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            contextTier: $data['contextTier'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modelId' => $this->modelId,
            'reasoningEffort' => $this->reasoningEffort,
            'contextTier' => $this->contextTier,
        ], fn ($v) => $v !== null);
    }
}
