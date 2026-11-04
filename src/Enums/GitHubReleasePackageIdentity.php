<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubReleasePackageIdentity: string
{
    case Dev = 'dev';
    case Release = 'release';
}
