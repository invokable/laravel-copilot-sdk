<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A changed file and its unified diff.
 *
 * @experimental
 */
readonly class WorkspaceDiffFileChange implements Arrayable
{
    public function __construct(
        public string $changeType,
        public string $diff,
        public string $path,
        public ?bool $isTruncated = null,
        public ?string $oldPath = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            changeType: $data['changeType'] ?? '',
            diff: $data['diff'] ?? '',
            path: $data['path'] ?? '',
            isTruncated: $data['isTruncated'] ?? null,
            oldPath: $data['oldPath'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'changeType' => $this->changeType,
            'diff' => $this->diff,
            'isTruncated' => $this->isTruncated,
            'oldPath' => $this->oldPath,
            'path' => $this->path,
        ], static fn ($value) => $value !== null);
    }
}
