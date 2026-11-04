<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\GitHubBranchPullRequestLookupStatus;
use Revolution\Copilot\Enums\GitHubFetchOperation;
use Revolution\Copilot\Enums\GitHubFetchResultKind;
use Revolution\Copilot\Enums\GitHubReleaseDownloadKind;
use Revolution\Copilot\Enums\GitHubReleaseLookupKind;
use Revolution\Copilot\Enums\GitHubReleasePackageIdentity;
use Revolution\Copilot\Enums\GitHubReleaseQuery;
use Revolution\Copilot\Enums\GitHubReleaseSnapshotOutcome;
use Revolution\Copilot\Enums\GitHubReleaseUpdateChannel;

it('matches upstream GitHub RPC enum values', function () {
    expect(array_map(static fn (GitHubFetchOperation $case): string => $case->value, GitHubFetchOperation::cases()))
        ->toBe([
            'repository-access',
            'branch-exists',
            'job-create',
            'job-status',
            'sessions-list',
            'pull-requests-graphql',
            'pull-request',
            'pull-request-files',
        ]);
    expect(array_map(static fn (GitHubFetchResultKind $case): string => $case->value, GitHubFetchResultKind::cases()))
        ->toBe(['response', 'circuit-breaker', 'proxy-response', 'failed']);
    expect(array_map(static fn (GitHubBranchPullRequestLookupStatus $case): string => $case->value, GitHubBranchPullRequestLookupStatus::cases()))
        ->toBe(['found', 'no-token', 'no-pr', 'lookup-failed']);
    expect(array_map(static fn (GitHubReleaseDownloadKind $case): string => $case->value, GitHubReleaseDownloadKind::cases()))
        ->toBe(['downloaded', 'http-error', 'digest-mismatch', 'cancelled', 'failed']);
    expect(array_map(static fn (GitHubReleaseLookupKind $case): string => $case->value, GitHubReleaseLookupKind::cases()))
        ->toBe(['found', 'not-found', 'no-release', 'rate-limited', 'failed']);
    expect(array_map(static fn (GitHubReleasePackageIdentity $case): string => $case->value, GitHubReleasePackageIdentity::cases()))
        ->toBe(['dev', 'release']);
    expect(array_map(static fn (GitHubReleaseQuery $case): string => $case->value, GitHubReleaseQuery::cases()))
        ->toBe(['latest-stable', 'latest-prerelease', 'latest-app', 'tag', 'release-list']);
    expect(array_map(static fn (GitHubReleaseSnapshotOutcome $case): string => $case->value, GitHubReleaseSnapshotOutcome::cases()))
        ->toBe(['success', 'failed']);
    expect(array_map(static fn (GitHubReleaseUpdateChannel $case): string => $case->value, GitHubReleaseUpdateChannel::cases()))
        ->toBe(['stable', 'prerelease']);
});
