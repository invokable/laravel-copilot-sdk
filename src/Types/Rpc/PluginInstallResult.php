<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of installing a plugin.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginInstallResult implements Arrayable
{
    public function __construct(
        public InstalledPluginInfo $plugin,
        public int $skillsInstalled,
        public ?string $deprecationWarning = null,
        public ?string $postInstallMessage = null,
        public ?string $stagingMode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugin: InstalledPluginInfo::fromArray($data['plugin'] ?? []),
            skillsInstalled: Arr::integer($data, 'skillsInstalled'),
            deprecationWarning: $data['deprecationWarning'] ?? null,
            postInstallMessage: $data['postInstallMessage'] ?? null,
            stagingMode: $data['stagingMode'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'plugin' => $this->plugin->toArray(),
            'skillsInstalled' => $this->skillsInstalled,
            'deprecationWarning' => $this->deprecationWarning,
            'postInstallMessage' => $this->postInstallMessage,
            'stagingMode' => $this->stagingMode,
        ], fn ($value) => $value !== null);
    }
}
