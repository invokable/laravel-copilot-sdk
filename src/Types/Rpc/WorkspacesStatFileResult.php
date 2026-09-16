<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Filesystem metadata for a path in the session workspace files directory.
 *
 * @experimental
 */
readonly class WorkspacesStatFileResult implements Arrayable
{
    /**
     * @param  bool  $isFile  Whether the path identifies a regular file
     * @param  bool  $isDirectory  Whether the path identifies a directory
     * @param  float  $size  Size in bytes
     * @param  float  $mtimeMs  Last modification time in Unix epoch milliseconds
     * @param  float  $birthtimeMs  Creation time in Unix epoch milliseconds
     */
    public function __construct(
        public bool $isFile,
        public bool $isDirectory,
        public float $size,
        public float $mtimeMs,
        public float $birthtimeMs,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            isFile: Arr::boolean($data, 'isFile', false),
            isDirectory: Arr::boolean($data, 'isDirectory', false),
            size: (float) ($data['size'] ?? 0),
            mtimeMs: (float) ($data['mtimeMs'] ?? 0),
            birthtimeMs: (float) ($data['birthtimeMs'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'isFile' => $this->isFile,
            'isDirectory' => $this->isDirectory,
            'size' => $this->size,
            'mtimeMs' => $this->mtimeMs,
            'birthtimeMs' => $this->birthtimeMs,
        ];
    }
}
