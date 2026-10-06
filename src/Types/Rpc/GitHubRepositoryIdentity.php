<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class GitHubRepositoryIdentity implements Arrayable
{
    public function __construct(
        public string $host,
        public string $name,
        public string $owner,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            host: $data['host'] ?? '',
            name: $data['name'] ?? '',
            owner: $data['owner'] ?? '',
        );
    }

    public function toArray(): array
    {
        return ['host' => $this->host, 'name' => $this->name, 'owner' => $this->owner];
    }
}
