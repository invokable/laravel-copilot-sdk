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
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            overridable: (bool) ($data['overridable'] ?? false),
            source: $data['source'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'overridable' => $this->overridable,
            'source' => $this->source,
        ];
    }
}
