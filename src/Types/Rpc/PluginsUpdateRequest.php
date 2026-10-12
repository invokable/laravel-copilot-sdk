<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Name or spec of the plugin to update.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginsUpdateRequest implements Arrayable
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(name: Arr::string($data, 'name'));
    }

    public function toArray(): array
    {
        return ['name' => $this->name];
    }
}
