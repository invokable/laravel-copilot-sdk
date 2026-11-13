<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HostType;

/**
 * Working-directory context recognized by a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionMetadataGetWorkingDirectoryContextResult implements Arrayable
{
    public function __construct(
        public string $cwd,
        public ?string $gitRoot = null,
        public ?string $repository = null,
        public HostType|string|null $hostType = null,
        public ?string $repositoryHost = null,
        public ?string $branch = null,
        public ?string $defaultBranch = null,
        public ?string $headCommit = null,
        public ?string $baseCommit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $hostType = isset($data['hostType']) ? Arr::string($data, 'hostType') : null;

        return new self(
            cwd: Arr::string($data, 'cwd', ''),
            gitRoot: isset($data['gitRoot']) ? Arr::string($data, 'gitRoot') : null,
            repository: isset($data['repository']) ? Arr::string($data, 'repository') : null,
            hostType: $hostType !== null ? (HostType::tryFrom($hostType) ?? $hostType) : null,
            repositoryHost: isset($data['repositoryHost']) ? Arr::string($data, 'repositoryHost') : null,
            branch: isset($data['branch']) ? Arr::string($data, 'branch') : null,
            defaultBranch: isset($data['defaultBranch']) ? Arr::string($data, 'defaultBranch') : null,
            headCommit: isset($data['headCommit']) ? Arr::string($data, 'headCommit') : null,
            baseCommit: isset($data['baseCommit']) ? Arr::string($data, 'baseCommit') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cwd' => $this->cwd,
            'gitRoot' => $this->gitRoot,
            'repository' => $this->repository,
            'hostType' => $this->hostType instanceof HostType ? $this->hostType->value : $this->hostType,
            'repositoryHost' => $this->repositoryHost,
            'branch' => $this->branch,
            'defaultBranch' => $this->defaultBranch,
            'headCommit' => $this->headCommit,
            'baseCommit' => $this->baseCommit,
        ], fn ($value): bool => $value !== null);
    }
}
