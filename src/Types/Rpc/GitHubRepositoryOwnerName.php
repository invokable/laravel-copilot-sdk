<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class GitHubRepositoryOwnerName implements Arrayable
{
    public function __construct(
        public string $owner,
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            owner: Arr::string($data, 'owner', ''),
            name: Arr::string($data, 'name', ''),
        );
    }

    public function toArray(): array
    {
        return ['owner' => $this->owner, 'name' => $this->name];
    }
}
