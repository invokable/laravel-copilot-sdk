<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubReleaseUpdateChannel: string
{
    case Stable = 'stable';
    case Prerelease = 'prerelease';
}
