<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\GitHubReleaseDownloadKind;
use Revolution\Copilot\Enums\GitHubReleaseLookupKind;
use Revolution\Copilot\Enums\GitHubReleasePackageIdentity;
use Revolution\Copilot\Enums\GitHubReleaseQuery;
use Revolution\Copilot\Enums\GitHubReleaseSnapshotOutcome;
use Revolution\Copilot\Enums\GitHubReleaseUpdateChannel;
use Revolution\Copilot\Types\Rpc\GitHubReleaseAbandonRequestParams;
use Revolution\Copilot\Types\Rpc\GitHubReleaseAbandonRequestResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadAssetRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadAssetResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadProgressRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadProgressResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseLookupRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseLookupResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseRequestIdResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseSnapshotAsset;
use Revolution\Copilot\Types\Rpc\GitHubReleaseSnapshotEntry;
use Revolution\Copilot\Types\Rpc\GitHubReleaseSnapshotRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseSnapshotResult;

describe('GitHub release requests', function () {
    it('round-trips lookup, snapshot, download, and request-id payloads', function () {
        $lookup = new GitHubReleaseLookupRequest(
            requestId: 15,
            query: GitHubReleaseQuery::Tag,
            authInfo: ['type' => 'gh-cli'],
            value: 'v1.2.3',
        );
        $snapshot = new GitHubReleaseSnapshotRequest(
            requestId: 16,
            updateChannel: GitHubReleaseUpdateChannel::Prerelease,
            buildVersion: '1.0.0',
            cliVersionOverride: '1.1.0',
            packageIdentity: GitHubReleasePackageIdentity::Release,
        );
        $download = new GitHubReleaseDownloadAssetRequest(
            requestId: 17,
            assetUrl: 'https://github.com/octo/demo/releases/download/v1/demo.zip',
            authInfo: ['type' => 'gh-cli'],
            expectedSha256: str_repeat('a', 64),
            snapshotRequestId: 16,
        );

        expect(GitHubReleaseLookupRequest::fromArray($lookup->toArray())->toArray())->toBe($lookup->toArray())
            ->and(GitHubReleaseLookupRequest::fromArray($lookup->toArray())->query)->toBe(GitHubReleaseQuery::Tag)
            ->and(GitHubReleaseSnapshotRequest::fromArray($snapshot->toArray())->toArray())->toBe($snapshot->toArray())
            ->and(GitHubReleaseSnapshotRequest::fromArray($snapshot->toArray())->packageIdentity)->toBe(GitHubReleasePackageIdentity::Release)
            ->and(GitHubReleaseDownloadAssetRequest::fromArray($download->toArray())->toArray())->toBe($download->toArray())
            ->and(GitHubReleaseAbandonRequestParams::fromArray(['requestId' => 18])->toArray())->toBe(['requestId' => 18])
            ->and(GitHubReleaseDownloadProgressRequest::fromArray(['requestId' => 19])->toArray())->toBe(['requestId' => 19])
            ->and(GitHubReleaseRequestIdResult::fromArray(['requestId' => 20])->toArray())->toBe(['requestId' => 20]);
    });

    it('omits absent optional authentication and version fields', function () {
        expect((new GitHubReleaseLookupRequest(15, GitHubReleaseQuery::LatestStable))->toArray())
            ->toBe(['requestId' => 15, 'query' => 'latest-stable'])
            ->and((new GitHubReleaseSnapshotRequest(16, GitHubReleaseUpdateChannel::Stable, '1.0.0'))->toArray())
            ->toBe([
                'requestId' => 16,
                'updateChannel' => 'stable',
                'buildVersion' => '1.0.0',
            ]);
    });
});

describe('GitHub release results', function () {
    it('round-trips lookup and snapshot variants with nested release assets', function () {
        $lookup = GitHubReleaseLookupResult::fromArray([
            'kind' => 'found',
            'releaseJson' => '{"tag_name":"v1.2.3"}',
        ]);
        $entry = [
            'tag_name' => 'v1.2.3',
            'prerelease' => false,
            'draft' => false,
            'assets' => [[
                'name' => 'demo.zip',
                'browser_download_url' => 'https://github.com/octo/demo/releases/download/v1/demo.zip',
            ]],
        ];
        $snapshot = GitHubReleaseSnapshotResult::fromArray([
            'outcome' => 'success',
            'updateChannel' => 'stable',
            'lookupDurationMs' => 1.5,
            'latestStableReleases' => [$entry],
            'prereleasesNewerThanLatestStable' => [],
            'runningVersion' => '1.0.0',
            'eligible' => false,
            'automaticUpdateCandidate' => $entry,
        ]);
        $failedSnapshot = GitHubReleaseSnapshotResult::fromArray([
            'outcome' => 'failed',
            'updateChannel' => 'prerelease',
            'lookupDurationMs' => 2.5,
            'error' => 'GitHub is unavailable.',
        ]);

        expect($lookup->kind)->toBe(GitHubReleaseLookupKind::Found)
            ->and($lookup->toArray())->toBe(['kind' => 'found', 'releaseJson' => '{"tag_name":"v1.2.3"}'])
            ->and($snapshot->outcome)->toBe(GitHubReleaseSnapshotOutcome::Success)
            ->and($snapshot->latestStableReleases[0])->toBeInstanceOf(GitHubReleaseSnapshotEntry::class)
            ->and($snapshot->latestStableReleases[0]->assets[0])->toBeInstanceOf(GitHubReleaseSnapshotAsset::class)
            ->and($snapshot->latestStableReleases[0]->toArray())->toBe($entry)
            ->and($snapshot->lookupDurationMs)->toBe(1.5)
            ->and($snapshot->eligible)->toBeFalse()
            ->and($snapshot->toArray())->toBe([
                'outcome' => 'success',
                'updateChannel' => 'stable',
                'lookupDurationMs' => 1.5,
                'latestStableReleases' => [$entry],
                'prereleasesNewerThanLatestStable' => [],
                'runningVersion' => '1.0.0',
                'eligible' => false,
                'automaticUpdateCandidate' => $entry,
            ])
            ->and($failedSnapshot->outcome)->toBe(GitHubReleaseSnapshotOutcome::Failed)
            ->and($failedSnapshot->toArray())->toBe([
                'outcome' => 'failed',
                'updateChannel' => 'prerelease',
                'lookupDurationMs' => 2.5,
                'error' => 'GitHub is unavailable.',
            ]);
    });

    it('round-trips download, progress, and abandoned-request results', function () {
        $download = GitHubReleaseDownloadAssetResult::fromArray([
            'kind' => 'downloaded',
            'path' => '/tmp/release/demo.zip',
            'sizeBytes' => 2048,
            'sha256' => str_repeat('a', 64),
        ]);
        $progress = GitHubReleaseDownloadProgressResult::fromArray([
            'receivedBytes' => 1024,
            'totalBytes' => 2048,
        ]);
        $abandon = GitHubReleaseAbandonRequestResult::fromArray(['abandoned' => true]);

        expect($download->kind)->toBe(GitHubReleaseDownloadKind::Downloaded)
            ->and($download->toArray())->toBe([
                'kind' => 'downloaded',
                'path' => '/tmp/release/demo.zip',
                'sizeBytes' => 2048,
                'sha256' => str_repeat('a', 64),
            ])
            ->and($progress->toArray())->toBe(['receivedBytes' => 1024, 'totalBytes' => 2048])
            ->and((new GitHubReleaseDownloadProgressResult(0))->toArray())->toBe(['receivedBytes' => 0])
            ->and($abandon->toArray())->toBe(['abandoned' => true]);
    });
});
