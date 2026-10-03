<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Per-key lock state and provenance for managed setting values. */
readonly class ManagedSettingsMeta implements Arrayable
{
    public function __construct(
        public ?ManagedSettingMeta $model = null,
        public ?ManagedSettingMeta $autoTier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            model: isset($data['model']) ? ManagedSettingMeta::fromArray($data['model']) : null,
            autoTier: isset($data['autoTier']) ? ManagedSettingMeta::fromArray($data['autoTier']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model?->toArray(),
            'autoTier' => $this->autoTier?->toArray(),
        ], static fn ($value) => $value !== null);
    }
}
