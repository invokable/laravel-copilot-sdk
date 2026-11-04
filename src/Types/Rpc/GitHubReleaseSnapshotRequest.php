<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\GitHubReleasePackageIdentity;
use Revolution\Copilot\Enums\GitHubReleaseUpdateChannel;

/** @experimental */
readonly class GitHubReleaseSnapshotRequest implements Arrayable
{
    public function __construct(
        public int $requestId,
        public GitHubReleaseUpdateChannel $updateChannel,
        public string $buildVersion,
        public ?string $cliVersionOverride = null,
        public ?GitHubReleasePackageIdentity $packageIdentity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::integer($data, 'requestId', 0),
            updateChannel: GitHubReleaseUpdateChannel::from(Arr::string($data, 'updateChannel', '')),
            buildVersion: Arr::string($data, 'buildVersion', ''),
            cliVersionOverride: isset($data['cliVersionOverride']) ? Arr::string($data, 'cliVersionOverride') : null,
            packageIdentity: isset($data['packageIdentity'])
                ? GitHubReleasePackageIdentity::from(Arr::string($data, 'packageIdentity'))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'updateChannel' => $this->updateChannel->value,
            'buildVersion' => $this->buildVersion,
            'cliVersionOverride' => $this->cliVersionOverride,
            'packageIdentity' => $this->packageIdentity?->value,
        ], static fn ($value): bool => $value !== null);
    }
}
