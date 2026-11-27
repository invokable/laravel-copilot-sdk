<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class PluginsScanDirectoriesResult implements Arrayable
{
    /**
     * @param  array<int, InstalledPlugin>  $plugins
     * @param  array<int, PluginDirectoryMetadata>  $directories
     * @param  array<int, string>  $warnings
     */
    public function __construct(
        public array $plugins,
        public array $directories,
        public array $warnings,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugins: array_map(
                fn (array $plugin): InstalledPlugin => InstalledPlugin::fromArray($plugin),
                Arr::array($data, 'plugins'),
            ),
            directories: array_map(
                fn (array $directory): PluginDirectoryMetadata => PluginDirectoryMetadata::fromArray($directory),
                Arr::array($data, 'directories'),
            ),
            warnings: Arr::array($data, 'warnings'),
        );
    }

    public function toArray(): array
    {
        return [
            'plugins' => array_map(fn (InstalledPlugin $plugin): array => $plugin->toArray(), $this->plugins),
            'directories' => array_map(fn (PluginDirectoryMetadata $directory): array => $directory->toArray(), $this->directories),
            'warnings' => $this->warnings,
        ];
    }
}
