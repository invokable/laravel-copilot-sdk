<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

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
     * @param  ?SessionFsError  $error  Structured provider error, if the stat failed
     */
    public function __construct(
        public string $birthtime,
        public bool $isDirectory,
        public bool $isFile,
        public string $mtime,
        public int|float $size,
        public ?SessionFsError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            birthtime: Arr::string($data, 'birthtime', ''),
            isDirectory: Arr::boolean($data, 'isDirectory', false),
            isFile: Arr::boolean($data, 'isFile', false),
            mtime: Arr::string($data, 'mtime', ''),
            size: $data['size'] ?? 0,
            error: isset($data['error']) ? SessionFsError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'birthtime' => $this->birthtime,
            'isDirectory' => $this->isDirectory,
            'isFile' => $this->isFile,
            'mtime' => $this->mtime,
            'size' => $this->size,
        ];

        if ($this->error !== null) {
            $result['error'] = $this->error->toArray();
        }

        return $result;
    }
}
