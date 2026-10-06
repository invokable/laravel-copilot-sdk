<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\GitHubOwnersCancelRequest;
use Revolution\Copilot\Types\Rpc\GitHubOwnersCancelResult;
use Revolution\Copilot\Types\Rpc\GitHubOwnersListRequest;
use Revolution\Copilot\Types\Rpc\GitHubOwnersListResult;
use Revolution\Copilot\Types\Rpc\GitHubOwnersRequestIdResult;

/** Cancellable owner discovery for GitHub repository operations. */
class PendingServerGitHubOwners
{
    public function __construct(protected JsonRpcClient $client) {}

    public function nextRequestId(): GitHubOwnersRequestIdResult
    {
        return GitHubOwnersRequestIdResult::fromArray(
            $this->client->request('gitHubOwners.nextRequestId', []),
        );
    }

    public function list(GitHubOwnersListRequest|array $params): GitHubOwnersListResult
    {
        $paramsArray = ($params instanceof GitHubOwnersListRequest
            ? $params
            : GitHubOwnersListRequest::fromArray($params))->toArray();

        return GitHubOwnersListResult::fromArray(
            $this->client->request('gitHubOwners.list', $paramsArray),
        );
    }

    public function cancel(GitHubOwnersCancelRequest|array $params): GitHubOwnersCancelResult
    {
        $paramsArray = ($params instanceof GitHubOwnersCancelRequest
            ? $params
            : GitHubOwnersCancelRequest::fromArray($params))->toArray();

        return GitHubOwnersCancelResult::fromArray(
            $this->client->request('gitHubOwners.cancel', $paramsArray),
        );
    }
}
