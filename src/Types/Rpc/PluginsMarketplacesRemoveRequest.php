<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Name of the marketplace to remove and optional force flag.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginsMarketplacesRemoveRequest implements Arrayable
{
    public function __construct(
        public string $name,
        public ?bool $force = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            force: isset($data['force']) ? Arr::boolean($data, 'force') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'force' => $this->force,
        ], fn ($value) => $value !== null);
    }
}
