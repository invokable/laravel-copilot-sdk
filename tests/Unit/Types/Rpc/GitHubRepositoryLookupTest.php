<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\GitHubBranchPullRequestLookupStatus;
use Revolution\Copilot\Types\Rpc\GitHubBranchPullRequestLookupRequest;
use Revolution\Copilot\Types\Rpc\GitHubBranchPullRequestLookupResult;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryByIdRequest;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryByIdResult;
use Revolution\Copilot\Types\Rpc\GitHubRepositoryOwnerName;

describe('GitHub repository lookup requests', function () {
    it('round-trips repository id and branch pull request parameters', function () {
        $byId = new GitHubRepositoryByIdRequest(
            authInfo: ['type' => 'gh-cli'],
            repositoryId: 123,
            authContextId: 'account-1',
        );
        $pullRequest = new GitHubBranchPullRequestLookupRequest(
            authInfo: ['type' => 'gh-cli'],
            headOwner: 'octo',
            baseRepositories: [new GitHubRepositoryOwnerName('octo', 'demo')],
            branch: 'feature',
            includeClosed: false,
        );

        expect(GitHubRepositoryByIdRequest::fromArray($byId->toArray())->toArray())
            ->toBe($byId->toArray())
            ->and((new GitHubRepositoryByIdRequest(['type' => 'gh-cli'], 123))->toArray())
            ->toBe(['authInfo' => ['type' => 'gh-cli'], 'repositoryId' => 123])
            ->and(GitHubBranchPullRequestLookupRequest::fromArray($pullRequest->toArray())->toArray())
            ->toBe($pullRequest->toArray())
            ->and(GitHubBranchPullRequestLookupRequest::fromArray($pullRequest->toArray())->baseRepositories[0])
            ->toBeInstanceOf(GitHubRepositoryOwnerName::class);
    });

    it('returns typed repository details and nullable branch pull request data', function () {
        $repository = GitHubRepositoryByIdResult::fromArray([
            'repository' => ['owner' => 'octo', 'name' => 'demo'],
        ]);
        $missingRepository = GitHubRepositoryByIdResult::fromArray(['repository' => null]);
        $foundPullRequest = GitHubBranchPullRequestLookupResult::fromArray([
            'status' => 'found',
            'pullRequest' => ['number' => 42, 'title' => 'Add feature'],
        ]);
        $noPullRequest = GitHubBranchPullRequestLookupResult::fromArray([
            'status' => 'no-pr',
        ]);

        expect($repository->repository)
            ->toBeInstanceOf(GitHubRepositoryOwnerName::class)
            ->and($repository->toArray())->toBe(['repository' => ['owner' => 'octo', 'name' => 'demo']])
            ->and($missingRepository->toArray())->toBe(['repository' => null])
            ->and($foundPullRequest->status)->toBe(GitHubBranchPullRequestLookupStatus::Found)
            ->and($foundPullRequest->pullRequest)->toBe(['number' => 42, 'title' => 'Add feature'])
            ->and($foundPullRequest->toArray())->toBe([
                'status' => 'found',
                'pullRequest' => ['number' => 42, 'title' => 'Add feature'],
            ])
            ->and($noPullRequest->status)->toBe(GitHubBranchPullRequestLookupStatus::NoPr)
            ->and($noPullRequest->pullRequest)->toBeNull();
    });

    it('preserves unknown branch pull request lookup status strings', function () {
        $result = GitHubBranchPullRequestLookupResult::fromArray(['status' => 'future-status']);

        expect($result->status)->toBe('future-status')
            ->and($result->toArray())->toBe([
                'status' => 'future-status',
                'pullRequest' => null,
            ]);
    });
});
