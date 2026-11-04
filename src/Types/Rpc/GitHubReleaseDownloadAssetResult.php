<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\GitHubReleaseDownloadKind;

/** @experimental */
readonly class GitHubReleaseDownloadAssetResult implements Arrayable
{
    public function __construct(
        public GitHubReleaseDownloadKind $kind,
        public ?string $path = null,
        public ?int $sizeBytes = null,
        public ?string $sha256 = null,
        public ?int $status = null,
        public ?string $statusText = null,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: GitHubReleaseDownloadKind::from(Arr::string($data, 'kind', '')),
            path: isset($data['path']) ? Arr::string($data, 'path') : null,
            sizeBytes: isset($data['sizeBytes']) ? Arr::integer($data, 'sizeBytes') : null,
            sha256: isset($data['sha256']) ? Arr::string($data, 'sha256') : null,
            status: isset($data['status']) ? Arr::integer($data, 'status') : null,
            statusText: isset($data['statusText']) ? Arr::string($data, 'statusText') : null,
            message: isset($data['message']) ? Arr::string($data, 'message') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'path' => $this->path,
            'sizeBytes' => $this->sizeBytes,
            'sha256' => $this->sha256,
            'status' => $this->status,
            'statusText' => $this->statusText,
            'message' => $this->message,
        ], static fn ($value): bool => $value !== null);
    }
}
