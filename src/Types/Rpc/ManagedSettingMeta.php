<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Lock state and provenance for one managed setting. */
readonly class ManagedSettingMeta implements Arrayable
{
    public function __construct(
        public bool $overridable,
        public string $source,
        public ?string $requested = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            overridable: (bool) ($data['overridable'] ?? false),
            source: $data['source'] ?? '',
            requested: $data['requested'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'overridable' => $this->overridable,
            'source' => $this->source,
            'requested' => $this->requested,
        ], static fn ($value) => $value !== null);
    }
}
