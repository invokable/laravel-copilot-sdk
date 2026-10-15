<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * One file included in the reviewed plugin installation.
 *
 * @experimental
 */
readonly class PluginInstallationFileReview implements Arrayable
{
    public function __construct(
        public string $digest,
        public bool $executable,
        public string $mediaType,
        public string $path,
        public int $sizeBytes,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            digest: $data['digest'],
            executable: $data['executable'],
            mediaType: $data['mediaType'],
            path: $data['path'],
            sizeBytes: $data['sizeBytes'],
        );
    }

    public function toArray(): array
    {
        return [
            'digest' => $this->digest,
            'executable' => $this->executable,
            'mediaType' => $this->mediaType,
            'path' => $this->path,
            'sizeBytes' => $this->sizeBytes,
        ];
    }
}
