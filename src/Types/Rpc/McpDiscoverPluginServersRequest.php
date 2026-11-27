<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class McpDiscoverPluginServersRequest implements Arrayable
{
    /**
     * @param  array<int, InstalledPlugin|array<string, mixed>>  $installedPlugins
     * @param  ?array<int, string>  $builtinPluginNames
     */
    public function __construct(
        public array $installedPlugins,
        public ?array $builtinPluginNames = null,
        public ?string $configDir = null,
        public ?string $homeDirectory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            installedPlugins: array_map(
                static fn (InstalledPlugin|array $plugin): InstalledPlugin => $plugin instanceof InstalledPlugin
                    ? $plugin
                    : InstalledPlugin::fromArray($plugin),
                Arr::array($data, 'installedPlugins'),
            ),
            builtinPluginNames: isset($data['builtinPluginNames']) ? Arr::array($data, 'builtinPluginNames') : null,
            configDir: isset($data['configDir']) ? Arr::string($data, 'configDir') : null,
            homeDirectory: isset($data['homeDirectory']) ? Arr::string($data, 'homeDirectory') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'installedPlugins' => array_map(
                static fn (InstalledPlugin|array $plugin): array => $plugin instanceof InstalledPlugin
                    ? $plugin->toArray()
                    : InstalledPlugin::fromArray($plugin)->toArray(),
                $this->installedPlugins,
            ),
            'builtinPluginNames' => $this->builtinPluginNames,
            'configDir' => $this->configDir,
            'homeDirectory' => $this->homeDirectory,
        ], fn ($value): bool => $value !== null);
    }
}
