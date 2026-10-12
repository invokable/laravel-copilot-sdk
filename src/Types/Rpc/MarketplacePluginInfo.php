<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Plugin entry advertised by a marketplace.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class MarketplacePluginInfo implements Arrayable
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
        ], fn ($value) => $value !== null);
    }
}
