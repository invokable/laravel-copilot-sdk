<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubReleaseQuery: string
{
    case LatestStable = 'latest-stable';
    case LatestPrerelease = 'latest-prerelease';
    case LatestApp = 'latest-app';
    case Tag = 'tag';
    case ReleaseList = 'release-list';
}
