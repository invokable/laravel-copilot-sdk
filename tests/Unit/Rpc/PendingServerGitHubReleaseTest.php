<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\GitHubReleaseDownloadKind;
use Revolution\Copilot\Enums\GitHubReleaseLookupKind;
use Revolution\Copilot\Enums\GitHubReleaseQuery;
use Revolution\Copilot\Enums\GitHubReleaseUpdateChannel;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadProgressRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseLookupRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseSnapshotRequest;

describe('PendingServerGitHubRelease', function () {
    it('dispatches release lookup, snapshot, download, and progress operations', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubRelease.allocateRequestId', [])
            ->andReturn(['requestId' => 42]);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubRelease.abandonRequest', ['requestId' => 11])
            ->andReturn(['abandoned' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubRelease.lookup', [
                'requestId' => 42,
                'query' => 'tag',
                'authInfo' => ['type' => 'gh-cli'],
                'value' => 'v1.2.3',
            ])
            ->andReturn([
                'kind' => 'found',
                'releaseJson' => '{"tag_name":"v1.2.3"}',
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubRelease.getSnapshot', [
                'requestId' => 43,
                'updateChannel' => 'stable',
                'buildVersion' => '1.0.0',
            ])
            ->andReturn([
                'outcome' => 'success',
                'updateChannel' => 'stable',
                'lookupDurationMs' => 2.5,
                'latestStableReleases' => [],
                'prereleasesNewerThanLatestStable' => [],
                'runningVersion' => '1.0.0',
                'eligible' => true,
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubRelease.downloadAsset', [
                'requestId' => 44,
                'assetUrl' => 'https://github.com/octo/demo/releases/download/v1/demo.zip',
                'expectedSha256' => str_repeat('a', 64),
            ])
            ->andReturn([
                'kind' => 'downloaded',
                'path' => '/tmp/release/demo.zip',
                'sizeBytes' => 2048,
                'sha256' => str_repeat('a', 64),
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubRelease.getDownloadProgress', ['requestId' => 44])
            ->andReturn(['receivedBytes' => 512, 'totalBytes' => 2048]);

        $release = (new ServerRpc($client))->gitHubRelease();
        $requestId = $release->allocateRequestId();
        $abandon = $release->abandonRequest(['requestId' => 11]);
        $lookup = $release->lookup(new GitHubReleaseLookupRequest(
            requestId: $requestId->requestId,
            query: GitHubReleaseQuery::Tag,
            authInfo: ['type' => 'gh-cli'],
            value: 'v1.2.3',
        ));
        $snapshot = $release->getSnapshot(new GitHubReleaseSnapshotRequest(
            requestId: 43,
            updateChannel: GitHubReleaseUpdateChannel::Stable,
            buildVersion: '1.0.0',
        ));
        $download = $release->downloadAsset([
            'requestId' => 44,
            'assetUrl' => 'https://github.com/octo/demo/releases/download/v1/demo.zip',
            'expectedSha256' => str_repeat('a', 64),
        ]);
        $progress = $release->getDownloadProgress(new GitHubReleaseDownloadProgressRequest(44));

        expect($requestId->requestId)->toBe(42)
            ->and($abandon->abandoned)->toBeTrue()
            ->and($lookup->kind)->toBe(GitHubReleaseLookupKind::Found)
            ->and($lookup->releaseJson)->toBe('{"tag_name":"v1.2.3"}')
            ->and($snapshot->lookupDurationMs)->toBe(2.5)
            ->and($snapshot->eligible)->toBeTrue()
            ->and($download->kind)->toBe(GitHubReleaseDownloadKind::Downloaded)
            ->and($download->path)->toBe('/tmp/release/demo.zip')
            ->and($progress->receivedBytes)->toBe(512)
            ->and($progress->totalBytes)->toBe(2048);
    });
});
