<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\GitHubFetchOperationRequest;
use Revolution\Copilot\Types\Rpc\GitHubFetchRequest;
use Revolution\Copilot\Types\Rpc\GitHubFetchRequestId;
use Revolution\Copilot\Types\Rpc\GitHubFetchResult;

/**
 * Authenticated GitHub API requests.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerGitHubFetch
{
    public function __construct(protected JsonRpcClient $client) {}

    public function allocateRequestId(): GitHubFetchRequestId
    {
        return GitHubFetchRequestId::fromArray(
            $this->client->request('gitHubFetch.allocateRequestId', []),
        );
    }

    public function request(GitHubFetchRequest|array $params): GitHubFetchResult
    {
        $paramsArray = ($params instanceof GitHubFetchRequest
            ? $params
            : GitHubFetchRequest::fromArray($params))->toArray();

        return GitHubFetchResult::fromArray(
            $this->client->request('gitHubFetch.request', $paramsArray),
        );
    }

    public function executeOperation(GitHubFetchOperationRequest|array $params): GitHubFetchResult
    {
        $paramsArray = ($params instanceof GitHubFetchOperationRequest
            ? $params
            : GitHubFetchOperationRequest::fromArray($params))->toArray();

        return GitHubFetchResult::fromArray(
            $this->client->request('gitHubFetch.executeOperation', $paramsArray),
        );
    }
}
