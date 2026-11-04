<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\GitHubBranchPullRequestLookupRequest;
use Revolution\Copilot\Types\Rpc\GitHubBranchPullRequestLookupResult;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryAtPathRequest;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryAtPathResult;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryByIdRequest;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryByIdResult;

/** GitHub repository identity and branch pull request lookups. */
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

    public function getById(GitHubRepositoryByIdRequest|array $params): GitHubRepositoryByIdResult
    {
        $paramsArray = ($params instanceof GitHubRepositoryByIdRequest
            ? $params
            : GitHubRepositoryByIdRequest::fromArray($params))->toArray();

        return GitHubRepositoryByIdResult::fromArray(
            $this->client->request('gitHubRepository.getById', $paramsArray),
        );
    }

    public function lookupBranchPullRequest(
        GitHubBranchPullRequestLookupRequest|array $params,
    ): GitHubBranchPullRequestLookupResult {
        $paramsArray = ($params instanceof GitHubBranchPullRequestLookupRequest
            ? $params
            : GitHubBranchPullRequestLookupRequest::fromArray($params))->toArray();

        return GitHubBranchPullRequestLookupResult::fromArray(
            $this->client->request('gitHubRepository.lookupBranchPullRequest', $paramsArray),
        );
    }
}
