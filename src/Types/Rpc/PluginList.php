<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of listing plugins.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class PluginList implements Arrayable
{
    /**
     * @param  array<PluginInfo>  $plugins  Installed plugins
     */
    public function __construct(
        public array $plugins,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugins: array_map(
                fn (array $plugin) => PluginInfo::fromArray($plugin),
                $data['plugins'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'plugins' => array_map(fn (PluginInfo $plugin) => $plugin->toArray(), $this->plugins),
        ];
    }
}
