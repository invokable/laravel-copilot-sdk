<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubFetchPinnedAddress implements Arrayable
{
    public function __construct(public string $address) {}

    public static function fromArray(array $data): self
    {
        return new self(address: Arr::string($data, 'address', ''));
    }

    public function toArray(): array
    {
        return ['address' => $this->address];
    }
}
