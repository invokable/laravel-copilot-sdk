<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubBranchPullRequestLookupStatus: string
{
    case Found = 'found';
    case NoToken = 'no-token';
    case NoPr = 'no-pr';
    case LookupFailed = 'lookup-failed';
}
