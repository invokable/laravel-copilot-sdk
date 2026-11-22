<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Plugin search results and errors from configured marketplaces.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginMarketplaceSearchResult implements Arrayable
{
    /**
     * @param  array<PluginMarketplaceSearchEntry>  $plugins
     * @param  array<PluginMarketplaceSearchError>  $errors
     */
    public function __construct(
        public array $plugins,
        public array $errors,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugins: array_map(
                fn (array $plugin) => PluginMarketplaceSearchEntry::fromArray($plugin),
                $data['plugins'] ?? [],
            ),
            errors: array_map(
                fn (array $error) => PluginMarketplaceSearchError::fromArray($error),
                $data['errors'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'plugins' => array_map(
                fn (PluginMarketplaceSearchEntry $plugin) => $plugin->toArray(),
                $this->plugins,
            ),
            'errors' => array_map(
                fn (PluginMarketplaceSearchError $error) => $error->toArray(),
                $this->errors,
            ),
        ];
    }
}
