<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Pre-resolved working-directory context carried by local session metadata. */
readonly class LocalSessionContext implements Arrayable
{
    public function __construct(
        public string $cwd,
        public ?string $gitRoot = null,
        public ?string $repository = null,
        public ?string $hostType = null,
        public ?string $branch = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cwd: Arr::string($data, 'cwd'),
            gitRoot: $data['gitRoot'] ?? null,
            repository: $data['repository'] ?? null,
            hostType: $data['hostType'] ?? null,
            branch: $data['branch'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cwd' => $this->cwd,
            'gitRoot' => $this->gitRoot,
            'repository' => $this->repository,
            'hostType' => $this->hostType,
            'branch' => $this->branch,
        ], fn ($value): bool => $value !== null);
    }
}
