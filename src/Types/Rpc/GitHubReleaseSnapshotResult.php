<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\GitHubReleaseSnapshotOutcome;
use Revolution\Copilot\Enums\GitHubReleaseUpdateChannel;

/** @experimental */
readonly class GitHubReleaseSnapshotResult implements Arrayable
{
    /**
     * @param  GitHubReleaseSnapshotEntry[]|null  $latestStableReleases
     * @param  GitHubReleaseSnapshotEntry[]|null  $prereleasesNewerThanLatestStable
     */
    public function __construct(
        public GitHubReleaseSnapshotOutcome $outcome,
        public GitHubReleaseUpdateChannel $updateChannel,
        public float $lookupDurationMs,
        public ?array $latestStableReleases = null,
        public ?array $prereleasesNewerThanLatestStable = null,
        public ?string $runningVersion = null,
        public ?bool $eligible = null,
        public ?GitHubReleaseSnapshotEntry $automaticUpdateCandidate = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            outcome: GitHubReleaseSnapshotOutcome::from(Arr::string($data, 'outcome', '')),
            updateChannel: GitHubReleaseUpdateChannel::from(Arr::string($data, 'updateChannel', '')),
            lookupDurationMs: Arr::float($data, 'lookupDurationMs', 0.0),
            latestStableReleases: isset($data['latestStableReleases'])
                ? array_map(
                    static fn (array $release): GitHubReleaseSnapshotEntry => GitHubReleaseSnapshotEntry::fromArray($release),
                    Arr::array($data, 'latestStableReleases'),
                )
                : null,
            prereleasesNewerThanLatestStable: isset($data['prereleasesNewerThanLatestStable'])
                ? array_map(
                    static fn (array $release): GitHubReleaseSnapshotEntry => GitHubReleaseSnapshotEntry::fromArray($release),
                    Arr::array($data, 'prereleasesNewerThanLatestStable'),
                )
                : null,
            runningVersion: isset($data['runningVersion']) ? Arr::string($data, 'runningVersion') : null,
            eligible: isset($data['eligible']) ? Arr::boolean($data, 'eligible') : null,
            automaticUpdateCandidate: isset($data['automaticUpdateCandidate'])
                ? GitHubReleaseSnapshotEntry::fromArray($data['automaticUpdateCandidate'])
                : null,
            error: isset($data['error']) ? Arr::string($data, 'error') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'outcome' => $this->outcome->value,
            'updateChannel' => $this->updateChannel->value,
            'lookupDurationMs' => $this->lookupDurationMs,
            'latestStableReleases' => $this->latestStableReleases === null
                ? null
                : array_map(
                    static fn (GitHubReleaseSnapshotEntry $release): array => $release->toArray(),
                    $this->latestStableReleases,
                ),
            'prereleasesNewerThanLatestStable' => $this->prereleasesNewerThanLatestStable === null
                ? null
                : array_map(
                    static fn (GitHubReleaseSnapshotEntry $release): array => $release->toArray(),
                    $this->prereleasesNewerThanLatestStable,
                ),
            'runningVersion' => $this->runningVersion,
            'eligible' => $this->eligible,
            'automaticUpdateCandidate' => $this->automaticUpdateCandidate?->toArray(),
            'error' => $this->error,
        ], static fn ($value): bool => $value !== null);
    }
}
