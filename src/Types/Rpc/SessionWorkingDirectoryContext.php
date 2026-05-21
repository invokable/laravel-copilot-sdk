<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\HostType;

/**
 * Working-directory and git context for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionWorkingDirectoryContext implements Arrayable
{
    /**
     * @param  ?string  $gitRoot  Resolved git root for cwd
     * @param  ?string  $repository  Repository identifier from remote URL
     * @param  ?HostType  $hostType  Hosting platform type
     * @param  ?string  $repositoryHost  Raw repository host (e.g. github.com)
     * @param  ?string  $branch  Current branch name
     * @param  ?string  $headCommit  Head commit SHA
     * @param  ?string  $baseCommit  Merge-base commit SHA
     */
    public function __construct(
        public string $cwd,
        public ?string $gitRoot = null,
        public ?string $repository = null,
        public ?HostType $hostType = null,
        public ?string $repositoryHost = null,
        public ?string $branch = null,
        public ?string $headCommit = null,
        public ?string $baseCommit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cwd: $data['cwd'] ?? '',
            gitRoot: $data['gitRoot'] ?? null,
            repository: $data['repository'] ?? null,
            hostType: isset($data['hostType']) ? HostType::tryFrom($data['hostType']) : null,
            repositoryHost: $data['repositoryHost'] ?? null,
            branch: $data['branch'] ?? null,
            headCommit: $data['headCommit'] ?? null,
            baseCommit: $data['baseCommit'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cwd' => $this->cwd,
            'gitRoot' => $this->gitRoot,
            'repository' => $this->repository,
            'hostType' => $this->hostType?->value,
            'repositoryHost' => $this->repositoryHost,
            'branch' => $this->branch,
            'headCommit' => $this->headCommit,
            'baseCommit' => $this->baseCommit,
        ], fn ($value): bool => $value !== null);
    }
}
