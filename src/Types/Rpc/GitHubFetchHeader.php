<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubFetchHeader implements Arrayable
{
    public function __construct(
        public string $name,
        public string $value,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name', ''),
            value: Arr::string($data, 'value', ''),
        );
    }

    public function toArray(): array
    {
        return ['name' => $this->name, 'value' => $this->value];
    }
}
