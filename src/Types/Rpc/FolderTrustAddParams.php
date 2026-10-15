<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Path to add to trusted folders.
 */
readonly class FolderTrustAddParams implements Arrayable
{
    public function __construct(
        public string $path,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(path: Arr::string($data, 'path'));
    }

    public function toArray(): array
    {
        return ['path' => $this->path];
    }
}
