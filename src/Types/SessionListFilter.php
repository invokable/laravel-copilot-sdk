<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Filter options for listing sessions.
 */
readonly class SessionListFilter implements Arrayable
{
    public function __construct(
        public ?string $cwd = null,
        public ?string $gitRoot = null,
        public ?string $repository = null,
        public ?string $branch = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cwd: $data['cwd'] ?? null,
            gitRoot: $data['gitRoot'] ?? null,
            repository: $data['repository'] ?? null,
            branch: $data['branch'] ?? null,
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
        ], fn ($value) => $value !== null);
    }
}
