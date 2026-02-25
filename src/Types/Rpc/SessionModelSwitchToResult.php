<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of switching session model.
 */
readonly class SessionModelSwitchToResult implements Arrayable
{
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
