<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional marketplace name; omit it to refresh all marketplaces.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginsMarketplacesRefreshRequest implements Arrayable
{
    public function __construct(
        public ?string $name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(name: $data['name'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['name' => $this->name], fn ($value) => $value !== null);
    }
}
