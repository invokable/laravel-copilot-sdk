<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class McpTaskMetadata implements Arrayable
{
    public function __construct(
        public int|float|null $ttl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $ttl = $data['ttl'] ?? null;

        return new self(ttl: is_int($ttl) || is_float($ttl) ? $ttl : null);
    }

    public function toArray(): array
    {
        return array_filter([
            'ttl' => $this->ttl,
        ], static fn ($value): bool => $value !== null);
    }
}
