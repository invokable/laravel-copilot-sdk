<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Information about a plugin.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class PluginInfo implements Arrayable
{
    /**
     * @param  string  $name  Plugin name
     * @param  string  $marketplace  Marketplace the plugin came from
     * @param  bool  $enabled  Whether the plugin is currently enabled
     * @param  ?string  $version  Installed version
     */
    public function __construct(
        public string $name,
        public string $marketplace,
        public bool $enabled,
        public ?string $version = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            marketplace: $data['marketplace'],
            enabled: $data['enabled'],
            version: $data['version'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'marketplace' => $this->marketplace,
            'enabled' => $this->enabled,
            'version' => $this->version,
        ], fn ($v) => $v !== null);
    }
}
