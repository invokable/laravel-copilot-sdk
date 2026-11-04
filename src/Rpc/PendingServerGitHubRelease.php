<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\GitHubReleaseAbandonRequestParams;
use Revolution\Copilot\Types\Rpc\GitHubReleaseAbandonRequestResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadAssetRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadAssetResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadProgressRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseDownloadProgressResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseLookupRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseLookupResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseRequestIdResult;
use Revolution\Copilot\Types\Rpc\GitHubReleaseSnapshotRequest;
use Revolution\Copilot\Types\Rpc\GitHubReleaseSnapshotResult;

/**
 * GitHub release lookup, snapshot, and asset download operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerGitHubRelease
{
    public function __construct(protected JsonRpcClient $client) {}

    public function allocateRequestId(): GitHubReleaseRequestIdResult
    {
        return GitHubReleaseRequestIdResult::fromArray(
            $this->client->request('gitHubRelease.allocateRequestId', []),
        );
    }

    public function abandonRequest(GitHubReleaseAbandonRequestParams|array $params): GitHubReleaseAbandonRequestResult
    {
        $paramsArray = ($params instanceof GitHubReleaseAbandonRequestParams
            ? $params
            : GitHubReleaseAbandonRequestParams::fromArray($params))->toArray();

        return GitHubReleaseAbandonRequestResult::fromArray(
            $this->client->request('gitHubRelease.abandonRequest', $paramsArray),
        );
    }

    public function lookup(GitHubReleaseLookupRequest|array $params): GitHubReleaseLookupResult
    {
        $paramsArray = ($params instanceof GitHubReleaseLookupRequest
            ? $params
            : GitHubReleaseLookupRequest::fromArray($params))->toArray();

        return GitHubReleaseLookupResult::fromArray(
            $this->client->request('gitHubRelease.lookup', $paramsArray),
        );
    }

    public function getSnapshot(GitHubReleaseSnapshotRequest|array $params): GitHubReleaseSnapshotResult
    {
        $paramsArray = ($params instanceof GitHubReleaseSnapshotRequest
            ? $params
            : GitHubReleaseSnapshotRequest::fromArray($params))->toArray();

        return GitHubReleaseSnapshotResult::fromArray(
            $this->client->request('gitHubRelease.getSnapshot', $paramsArray),
        );
    }

    public function downloadAsset(GitHubReleaseDownloadAssetRequest|array $params): GitHubReleaseDownloadAssetResult
    {
        $paramsArray = ($params instanceof GitHubReleaseDownloadAssetRequest
            ? $params
            : GitHubReleaseDownloadAssetRequest::fromArray($params))->toArray();

        return GitHubReleaseDownloadAssetResult::fromArray(
            $this->client->request('gitHubRelease.downloadAsset', $paramsArray),
        );
    }

    public function getDownloadProgress(GitHubReleaseDownloadProgressRequest|array $params): GitHubReleaseDownloadProgressResult
    {
        $paramsArray = ($params instanceof GitHubReleaseDownloadProgressRequest
            ? $params
            : GitHubReleaseDownloadProgressRequest::fromArray($params))->toArray();

        return GitHubReleaseDownloadProgressResult::fromArray(
            $this->client->request('gitHubRelease.getDownloadProgress', $paramsArray),
        );
    }
}
