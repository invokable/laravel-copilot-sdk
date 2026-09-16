<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * File or directory to remove from the session workspace files directory.
 *
 * @experimental
 */
readonly class WorkspacesRemovePathRequest implements Arrayable
{
    /**
     * @param  string  $path  Slash-separated relative path within the workspace files directory
     * @param  ?bool  $recursive  Whether to remove directory contents recursively. Defaults to false.
     * @param  ?bool  $force  Whether a missing path should be treated as success. Defaults to false.
     */
    public function __construct(
        public string $path,
        public ?bool $recursive = null,
        public ?bool $force = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path'),
            recursive: $data['recursive'] ?? null,
            force: $data['force'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'path' => $this->path,
            'recursive' => $this->recursive,
            'force' => $this->force,
        ], fn ($v) => $v !== null);
    }
}
