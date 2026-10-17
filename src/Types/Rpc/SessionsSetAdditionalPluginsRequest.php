<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to replace the session manager's additional installed plugins. */
readonly class SessionsSetAdditionalPluginsRequest implements Arrayable
{
    /**
     * @param  array<int, InstalledPlugin|array<string, mixed>>  $plugins
     */
    public function __construct(
        public array $plugins,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugins: array_map(
                static fn (InstalledPlugin|array $plugin): InstalledPlugin => $plugin instanceof InstalledPlugin
                    ? $plugin
                    : InstalledPlugin::fromArray($plugin),
                $data['plugins'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'plugins' => array_map(
                static fn (InstalledPlugin|array $plugin): array => $plugin instanceof InstalledPlugin
                    ? $plugin->toArray()
                    : InstalledPlugin::fromArray($plugin)->toArray(),
                $this->plugins,
            ),
        ];
    }
}
