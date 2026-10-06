<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class GitReposFromRemotesResult implements Arrayable
{
    /** @param GitRemoteRepository[] $repositories */
    public function __construct(public array $repositories = []) {}

    public static function fromArray(array $data): self
    {
        return new self(repositories: array_map(
            fn (array $repository) => GitRemoteRepository::fromArray($repository),
            $data['repositories'] ?? [],
        ));
    }

    public function toArray(): array
    {
        return ['repositories' => array_map(
            fn (GitRemoteRepository $repository) => $repository->toArray(),
            $this->repositories,
        )];
    }
}
