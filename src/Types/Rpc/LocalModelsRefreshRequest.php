<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Catalog refresh intent.
 *
 * @experimental
 */
readonly class LocalModelsRefreshRequest implements Arrayable
{
    public function __construct(
        public bool $background,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            background: $data['background'] ?? false,
        );
    }

    public function toArray(): array
    {
        return ['background' => $this->background];
    }
}
