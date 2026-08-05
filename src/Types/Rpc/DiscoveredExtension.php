<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\DiscoveredExtensionSource;

/**
 * Discovered extension metadata and persistent enablement state.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class DiscoveredExtension implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public string $path,
        public DiscoveredExtensionSource|string $source,
        public bool $enabled,
        public DiscoveredExtensionPlugin|array|null $plugin = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = $data['source'] ?? '';

        return new self(
            id: Arr::string($data, 'id'),
            name: Arr::string($data, 'name'),
            path: Arr::string($data, 'path'),
            source: DiscoveredExtensionSource::tryFrom($source) ?? $source,
            enabled: Arr::boolean($data, 'enabled'),
            plugin: isset($data['plugin'])
                ? ($data['plugin'] instanceof DiscoveredExtensionPlugin
                    ? $data['plugin']
                    : DiscoveredExtensionPlugin::fromArray($data['plugin']))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'source' => $this->source instanceof DiscoveredExtensionSource ? $this->source->value : $this->source,
            'enabled' => $this->enabled,
            'plugin' => $this->plugin instanceof DiscoveredExtensionPlugin ? $this->plugin->toArray() : $this->plugin,
        ], fn ($value): bool => $value !== null);
    }
}
