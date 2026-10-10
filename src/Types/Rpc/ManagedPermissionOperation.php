<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** URL operation evaluated against managed permissions. */
readonly class ManagedPermissionOperation implements Arrayable
{
    public function __construct(
        public string $url,
        public string $kind = 'url',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'] ?? '',
            kind: $data['kind'] ?? 'url',
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'kind' => $this->kind,
        ];
    }
}
