<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of switching session model.
 */
readonly class ModelSwitchToResult implements Arrayable
{
    /**
     * @param  ?string  $modelId  The model ID after switching, or null if using the default model
     * @param  ?bool  $deferred  Whether the switch was deferred because a model change was already queued
     */
    public function __construct(
        public ?string $modelId = null,
        public ?bool $deferred = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'] ?? null,
            deferred: $data['deferred'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modelId' => $this->modelId,
            'deferred' => $this->deferred,
        ], fn ($v) => $v !== null);
    }
}
