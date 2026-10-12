<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Plugins advertised by a marketplace.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class MarketplaceBrowseResult implements Arrayable
{
    /**
     * @param  array<MarketplacePluginInfo>  $plugins
     */
    public function __construct(
        public array $plugins,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugins: array_map(
                fn (array $plugin) => MarketplacePluginInfo::fromArray($plugin),
                $data['plugins'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'plugins' => array_map(
                fn (MarketplacePluginInfo $plugin) => $plugin->toArray(),
                $this->plugins,
            ),
        ];
    }
}
