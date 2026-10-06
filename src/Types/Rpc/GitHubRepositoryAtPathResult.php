<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class GitHubRepositoryAtPathResult implements Arrayable
{
    public function __construct(public ?GitHubRepositoryIdentity $repository = null) {}

    public static function fromArray(array $data): self
    {
        return new self(repository: isset($data['repository'])
            ? GitHubRepositoryIdentity::fromArray($data['repository'])
            : null);
    }

    public function toArray(): array
    {
        return ['repository' => $this->repository?->toArray()];
    }
}
