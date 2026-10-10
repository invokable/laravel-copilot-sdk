<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Per-plugin outcomes from retrying managed plugin preparation. */
readonly class SessionPluginsRetryManagedResult implements Arrayable
{
    /** @param array<ManagedPluginRetryEntry> $plugins */
    public function __construct(
        public array $plugins = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugins: array_map(
                static fn (ManagedPluginRetryEntry|array $plugin) => $plugin instanceof ManagedPluginRetryEntry
                    ? $plugin
                    : ManagedPluginRetryEntry::fromArray($plugin),
                $data['plugins'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'plugins' => array_map(
                static fn (ManagedPluginRetryEntry $plugin) => $plugin->toArray(),
                $this->plugins,
            ),
        ];
    }
}
