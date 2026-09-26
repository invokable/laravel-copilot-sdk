<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Support\Arr;

/**
 * Installed plugin details returned by the CLI.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class InstalledPluginInfo extends PluginInfo
{
    public function __construct(
        string $name,
        string $marketplace,
        bool $enabled,
        ?string $version = null,
        public ?string $directSourceId = null,
        public ?string $installedFrom = null,
        public ?string $source = null,
    ) {
        parent::__construct($name, $marketplace, $enabled, $version);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            marketplace: Arr::string($data, 'marketplace'),
            enabled: Arr::boolean($data, 'enabled'),
            version: $data['version'] ?? null,
            directSourceId: $data['directSourceId'] ?? null,
            installedFrom: $data['installedFrom'] ?? null,
            source: $data['source'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'directSourceId' => $this->directSourceId,
            'installedFrom' => $this->installedFrom,
            'source' => $this->source,
        ], fn ($value) => $value !== null);
    }
}
