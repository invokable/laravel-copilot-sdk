<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubReleaseLookupKind: string
{
    case Found = 'found';
    case NotFound = 'not-found';
    case NoRelease = 'no-release';
    case RateLimited = 'rate-limited';
    case Failed = 'failed';
}
