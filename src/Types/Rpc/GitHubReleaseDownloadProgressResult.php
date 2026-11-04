<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubReleaseDownloadProgressResult implements Arrayable
{
    public function __construct(
        public int $receivedBytes,
        public ?int $totalBytes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            receivedBytes: Arr::integer($data, 'receivedBytes', 0),
            totalBytes: isset($data['totalBytes']) ? Arr::integer($data, 'totalBytes') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'receivedBytes' => $this->receivedBytes,
            'totalBytes' => $this->totalBytes,
        ], static fn ($value): bool => $value !== null);
    }
}
