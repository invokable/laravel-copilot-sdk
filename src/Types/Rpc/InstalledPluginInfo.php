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
        public ?string $description = null,
        public ?InstalledPluginVerifiedSource $verifiedSource = null,
        public ?string $iconUrl = null,
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
            description: $data['description'] ?? null,
            verifiedSource: isset($data['verifiedSource']) ? InstalledPluginVerifiedSource::fromArray($data['verifiedSource']) : null,
            iconUrl: $data['iconUrl'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'directSourceId' => $this->directSourceId,
            'installedFrom' => $this->installedFrom,
            'source' => $this->source,
            'description' => $this->description,
            'verifiedSource' => $this->verifiedSource?->toArray(),
            'iconUrl' => $this->iconUrl,
        ], fn ($value) => $value !== null);
    }
}
