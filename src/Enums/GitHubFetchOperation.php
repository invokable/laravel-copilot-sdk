<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubFetchOperation: string
{
    case RepositoryAccess = 'repository-access';
    case BranchExists = 'branch-exists';
    case JobCreate = 'job-create';
    case JobStatus = 'job-status';
    case SessionsList = 'sessions-list';
    case PullRequestsGraphql = 'pull-requests-graphql';
    case PullRequest = 'pull-request';
    case PullRequestFiles = 'pull-request-files';
}
