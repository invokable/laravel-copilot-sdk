<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Directory path to add to the session's allowed directories.
 */
readonly class PermissionPathsAddParams implements Arrayable
{
    public function __construct(
        public string $path,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(path: Arr::string($data, 'path', ''));
    }

    public function toArray(): array
    {
        return ['path' => $this->path];
    }
}
