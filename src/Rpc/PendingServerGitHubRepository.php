<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryAtPathRequest;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryAtPathResult;

/** GitHub repository identity lookups for a local path. */
class PendingServerGitHubRepository
{
    public function __construct(protected JsonRpcClient $client) {}

    public function atPath(GitHubRepositoryAtPathRequest|array $params): GitHubRepositoryAtPathResult
    {
        $paramsArray = ($params instanceof GitHubRepositoryAtPathRequest
            ? $params
            : GitHubRepositoryAtPathRequest::fromArray($params))->toArray();

        return GitHubRepositoryAtPathResult::fromArray(
            $this->client->request('gitHubRepository.atPath', $paramsArray),
        );
    }
}
