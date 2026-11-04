<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubReleaseDownloadAssetRequest implements Arrayable
{
    public function __construct(
        public int $requestId,
        public string $assetUrl,
        public mixed $authInfo = null,
        public ?string $expectedSha256 = null,
        public ?int $snapshotRequestId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::integer($data, 'requestId', 0),
            assetUrl: Arr::string($data, 'assetUrl', ''),
            authInfo: $data['authInfo'] ?? null,
            expectedSha256: isset($data['expectedSha256']) ? Arr::string($data, 'expectedSha256') : null,
            snapshotRequestId: isset($data['snapshotRequestId']) ? Arr::integer($data, 'snapshotRequestId') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'assetUrl' => $this->assetUrl,
            'authInfo' => $this->authInfo,
            'expectedSha256' => $this->expectedSha256,
            'snapshotRequestId' => $this->snapshotRequestId,
        ], static fn ($value): bool => $value !== null);
    }
}
