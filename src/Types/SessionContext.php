<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HostType;

/**
 * Working directory context for a session.
 */
readonly class SessionContext implements Arrayable
{
    /**
     * @param  string  $cwd  Working directory where the session was created
     * @param  ?string  $gitRoot  Git repository root (if in a git repo)
     * @param  ?string  $repository  GitHub repository in "owner/repo" format
     * @param  HostType|string|null  $hostType  Repository hosting platform
     * @param  ?string  $branch  Current git branch
     */
    public function __construct(
        public string $cwd,
        public ?string $gitRoot = null,
        public ?string $repository = null,
        public ?string $branch = null,
        public HostType|string|null $hostType = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cwd: Arr::string($data, 'cwd'),
            gitRoot: $data['gitRoot'] ?? null,
            repository: $data['repository'] ?? null,
            branch: $data['branch'] ?? null,
            hostType: isset($data['hostType'])
                ? HostType::tryFrom($data['hostType']) ?? $data['hostType']
                : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'cwd' => $this->cwd,
            'gitRoot' => $this->gitRoot,
            'repository' => $this->repository,
            'branch' => $this->branch,
            'hostType' => $this->hostType instanceof HostType ? $this->hostType->value : $this->hostType,
        ], fn ($value) => $value !== null);
    }
}
