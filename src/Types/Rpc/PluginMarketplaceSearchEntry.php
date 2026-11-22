<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Plugin match returned from a marketplace search.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginMarketplaceSearchEntry implements Arrayable
{
    public function __construct(
        public string $name,
        public string $marketplace,
        public string $installSpec,
        public bool $installed,
        public ?PluginMarketplaceLiveState $live = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            marketplace: Arr::string($data, 'marketplace'),
            installSpec: Arr::string($data, 'installSpec'),
            installed: Arr::boolean($data, 'installed'),
            live: isset($data['live']) ? PluginMarketplaceLiveState::fromArray($data['live']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'marketplace' => $this->marketplace,
            'installSpec' => $this->installSpec,
            'installed' => $this->installed,
            'live' => $this->live?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
