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
        public mixed $metadata,
        public ?PluginMarketplaceLiveState $live = null,
        public ?string $description = null,
        public ?string $version = null,
        public ?string $sourceUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            marketplace: Arr::string($data, 'marketplace'),
            installSpec: Arr::string($data, 'installSpec'),
            installed: Arr::boolean($data, 'installed'),
            metadata: $data['metadata'],
            live: isset($data['live']) ? PluginMarketplaceLiveState::fromArray($data['live']) : null,
            description: isset($data['description']) ? Arr::string($data, 'description') : null,
            version: isset($data['version']) ? Arr::string($data, 'version') : null,
            sourceUrl: isset($data['sourceUrl']) ? Arr::string($data, 'sourceUrl') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'marketplace' => $this->marketplace,
            'installSpec' => $this->installSpec,
            'installed' => $this->installed,
            ...array_filter([
                'live' => $this->live?->toArray(),
                'description' => $this->description,
                'version' => $this->version,
                'sourceUrl' => $this->sourceUrl,
            ], fn ($value) => $value !== null),
            'metadata' => $this->metadata,
        ];
    }
}
