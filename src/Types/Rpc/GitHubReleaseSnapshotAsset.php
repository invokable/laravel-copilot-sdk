<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubReleaseSnapshotAsset implements Arrayable
{
    public function __construct(
        public string $name,
        public string $browserDownloadUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name', ''),
            browserDownloadUrl: Arr::string($data, 'browser_download_url', ''),
        );
    }

    public function toArray(): array
    {
        return ['name' => $this->name, 'browser_download_url' => $this->browserDownloadUrl];
    }
}
