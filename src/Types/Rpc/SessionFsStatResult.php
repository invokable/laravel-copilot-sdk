<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting file stats via SessionFs.
 */
readonly class SessionFsStatResult implements Arrayable
{
    /**
     * @param  string  $birthtime  ISO 8601 timestamp of creation
     * @param  bool  $isDirectory  Whether the path is a directory
     * @param  bool  $isFile  Whether the path is a file
     * @param  string  $mtime  ISO 8601 timestamp of last modification
     * @param  int|float  $size  File size in bytes
     */
    public function __construct(
        public string $birthtime,
        public bool $isDirectory,
        public bool $isFile,
        public string $mtime,
        public int|float $size,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            birthtime: $data['birthtime'] ?? '',
            isDirectory: $data['isDirectory'] ?? false,
            isFile: $data['isFile'] ?? false,
            mtime: $data['mtime'] ?? '',
            size: $data['size'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'birthtime' => $this->birthtime,
            'isDirectory' => $this->isDirectory,
            'isFile' => $this->isFile,
            'mtime' => $this->mtime,
            'size' => $this->size,
        ];
    }
}
