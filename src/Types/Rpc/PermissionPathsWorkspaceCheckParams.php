<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Path to evaluate against the workspace directory.
 */
readonly class PermissionPathsWorkspaceCheckParams implements Arrayable
{
    public function __construct(
        public string $path,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(path: $data['path'] ?? '');
    }

    public function toArray(): array
    {
        return ['path' => $this->path];
    }
}
