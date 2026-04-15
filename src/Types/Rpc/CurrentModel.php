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
     * @param  ?string  $modelId  The currently active model ID, or null if using the default model
     */
    public function __construct(
        public ?string $modelId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modelId' => $this->modelId,
        ], fn ($v) => $v !== null);
    }
}
