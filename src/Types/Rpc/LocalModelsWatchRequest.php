<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Acknowledged position in this connection's manager updates.
 *
 * @experimental
 */
readonly class LocalModelsWatchRequest implements Arrayable
{
    public function __construct(
        public int $afterRevision,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            afterRevision: (int) ($data['afterRevision'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return ['afterRevision' => $this->afterRevision];
    }
}
