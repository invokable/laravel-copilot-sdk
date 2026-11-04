<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubReleaseSnapshotEntry implements Arrayable
{
    /**
     * @param  GitHubReleaseSnapshotAsset[]  $assets
     */
    public function __construct(
        public string $tagName,
        public bool $prerelease,
        public bool $draft,
        public array $assets,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tagName: Arr::string($data, 'tag_name', ''),
            prerelease: Arr::boolean($data, 'prerelease', false),
            draft: Arr::boolean($data, 'draft', false),
            assets: array_map(
                static fn (array $asset): GitHubReleaseSnapshotAsset => GitHubReleaseSnapshotAsset::fromArray($asset),
                Arr::array($data, 'assets', []),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'tag_name' => $this->tagName,
            'prerelease' => $this->prerelease,
            'draft' => $this->draft,
            'assets' => array_map(static fn (GitHubReleaseSnapshotAsset $asset): array => $asset->toArray(), $this->assets),
        ];
    }
}
