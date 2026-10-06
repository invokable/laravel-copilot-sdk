<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Working-directory metadata and the client that produced it.
 */
readonly class SessionWorkingDirectoryContextWithClient implements Arrayable
{
    public function __construct(
        public string $cwd,
        public ?string $baseCommit = null,
        public ?string $branch = null,
        public ?string $clientName = null,
        public ?string $gitRoot = null,
        public ?string $headCommit = null,
        public ?string $hostType = null,
        public ?string $repository = null,
        public ?string $repositoryHost = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cwd: Arr::string($data, 'cwd'),
            baseCommit: $data['baseCommit'] ?? null,
            branch: $data['branch'] ?? null,
            clientName: $data['clientName'] ?? null,
            gitRoot: $data['gitRoot'] ?? null,
            headCommit: $data['headCommit'] ?? null,
            hostType: $data['hostType'] ?? null,
            repository: $data['repository'] ?? null,
            repositoryHost: $data['repositoryHost'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'baseCommit' => $this->baseCommit,
            'branch' => $this->branch,
            'clientName' => $this->clientName,
            'cwd' => $this->cwd,
            'gitRoot' => $this->gitRoot,
            'headCommit' => $this->headCommit,
            'hostType' => $this->hostType,
            'repository' => $this->repository,
            'repositoryHost' => $this->repositoryHost,
        ], static fn ($value) => $value !== null);
    }
}
