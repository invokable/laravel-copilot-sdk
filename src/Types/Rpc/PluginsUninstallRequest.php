<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Name or spec of the plugin to uninstall.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginsUninstallRequest implements Arrayable
{
    public function __construct(
        public string $name,
        public ?string $directSourceId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            directSourceId: $data['directSourceId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'directSourceId' => $this->directSourceId,
        ], fn ($value) => $value !== null);
    }
}
