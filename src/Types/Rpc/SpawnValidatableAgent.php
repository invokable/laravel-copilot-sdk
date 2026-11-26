<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SpawnValidatableAgent implements Arrayable
{
    public function __construct(public string $name, public ?string $id = null) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            id: isset($data['id']) ? Arr::string($data, 'id') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'id' => $this->id,
        ], static fn ($value): bool => $value !== null);
    }
}
