<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Bytes freed for each deleted session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionBulkDeleteResult implements Arrayable
{
    /**
     * @param  array<string, int>  $freedBytes
     */
    public function __construct(
        public array $freedBytes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(freedBytes: $data['freedBytes'] ?? []);
    }

    public function toArray(): array
    {
        return ['freedBytes' => $this->freedBytes];
    }
}
