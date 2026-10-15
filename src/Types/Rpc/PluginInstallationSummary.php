<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Summary of an installed, verified Agent Plugin.
 *
 * @experimental
 */
readonly class PluginInstallationSummary implements Arrayable
{
    public function __construct(
        public InstallationCatalogueIdentity $catalogue,
        public bool $enabled,
        public string $installedAt,
        public string $name,
        public string $operationId,
        public PluginInstallationSource $source,
        public PluginInstallationLocation $target,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            catalogue: InstallationCatalogueIdentity::fromArray($data['catalogue']),
            enabled: $data['enabled'],
            installedAt: $data['installedAt'],
            name: $data['name'],
            operationId: $data['operationId'],
            source: PluginInstallationSource::fromArray($data['source']),
            target: PluginInstallationLocation::fromArray($data['target']),
        );
    }

    public function toArray(): array
    {
        return [
            'catalogue' => $this->catalogue->toArray(),
            'enabled' => $this->enabled,
            'installedAt' => $this->installedAt,
            'name' => $this->name,
            'operationId' => $this->operationId,
            'source' => $this->source->toArray(),
            'target' => $this->target->toArray(),
        ];
    }
}
