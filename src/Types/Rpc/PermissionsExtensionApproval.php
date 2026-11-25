<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A user-scoped approval for an installed plugin extension and capability set.
 *
 * @experimental
 */
readonly class PermissionsExtensionApproval implements Arrayable
{
    /** @param  array<string>  $capabilities */
    public function __construct(
        public string $extensionName,
        public string $pluginName,
        public array $capabilities,
        public string $grantedAt,
        public string $installationKey,
        public ?string $installationLabel = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            extensionName: Arr::string($data, 'extensionName'),
            pluginName: Arr::string($data, 'pluginName'),
            capabilities: Arr::array($data, 'capabilities'),
            grantedAt: Arr::string($data, 'grantedAt'),
            installationKey: Arr::string($data, 'installationKey'),
            installationLabel: isset($data['installationLabel']) ? Arr::string($data, 'installationLabel') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'extensionName' => $this->extensionName,
            'pluginName' => $this->pluginName,
            'capabilities' => $this->capabilities,
            'grantedAt' => $this->grantedAt,
            'installationKey' => $this->installationKey,
            'installationLabel' => $this->installationLabel,
        ], fn ($value): bool => $value !== null);
    }
}
