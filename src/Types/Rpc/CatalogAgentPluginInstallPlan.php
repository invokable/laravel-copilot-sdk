<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogAgentPluginComponent;

/**
 * A reviewable description of what installing an Agent Plugin would do.
 *
 * @experimental
 */
readonly class CatalogAgentPluginInstallPlan implements Arrayable
{
    /**
     * @param  (CatalogAgentPluginMaterializationSource|CatalogAgentPluginMarketplaceSource|CatalogAgentPluginRepositorySource)[]  $sources
     * @param  CatalogAgentPluginComponent[]  $components
     * @param  string[]  $canvasIds
     * @param  string[]  $extensionIds
     * @param  string[]  $mcpDependencies
     * @param  string[]  $connectorDependencies
     */
    public function __construct(
        public array $sources,
        public ?string $pluginName,
        public array $components,
        public array $canvasIds,
        public array $extensionIds,
        public array $mcpDependencies,
        public array $connectorDependencies,
        public ?string $version = null,
        public ?string $commit = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $sources = array_map(
            fn (array $source): CatalogAgentPluginMaterializationSource|CatalogAgentPluginMarketplaceSource|CatalogAgentPluginRepositorySource => match ($source['kind'] ?? '') {
                'materialization' => CatalogAgentPluginMaterializationSource::fromArray($source),
                'marketplace' => CatalogAgentPluginMarketplaceSource::fromArray($source),
                'repository' => CatalogAgentPluginRepositorySource::fromArray($source),
            },
            $data['sources'] ?? [],
        );

        return new static(
            sources: $sources,
            pluginName: $data['pluginName'] ?? null,
            components: array_map(
                fn (string $component): CatalogAgentPluginComponent => CatalogAgentPluginComponent::from($component),
                $data['components'] ?? [],
            ),
            canvasIds: $data['canvasIds'] ?? [],
            extensionIds: $data['extensionIds'] ?? [],
            mcpDependencies: $data['mcpDependencies'] ?? [],
            connectorDependencies: $data['connectorDependencies'] ?? [],
            version: $data['version'] ?? null,
            commit: $data['commit'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'sources' => array_map(
                fn (CatalogAgentPluginMaterializationSource|CatalogAgentPluginMarketplaceSource|CatalogAgentPluginRepositorySource $source): array => $source->toArray(),
                $this->sources,
            ),
        ];

        if ($this->pluginName !== null) {
            $arr['pluginName'] = $this->pluginName;
        }
        if ($this->version !== null) {
            $arr['version'] = $this->version;
        }
        if ($this->commit !== null) {
            $arr['commit'] = $this->commit;
        }

        $arr['components'] = array_map(
            fn (CatalogAgentPluginComponent $component): string => $component->value,
            $this->components,
        );
        $arr['canvasIds'] = $this->canvasIds;
        $arr['extensionIds'] = $this->extensionIds;
        $arr['mcpDependencies'] = $this->mcpDependencies;
        $arr['connectorDependencies'] = $this->connectorDependencies;

        return $arr;
    }
}
