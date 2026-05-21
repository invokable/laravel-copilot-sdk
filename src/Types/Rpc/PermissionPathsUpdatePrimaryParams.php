<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Directory path to set as the primary permission path.
 */
readonly class PermissionPathsUpdatePrimaryParams implements Arrayable
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
