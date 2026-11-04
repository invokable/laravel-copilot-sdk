<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubReleaseDownloadKind: string
{
    case Downloaded = 'downloaded';
    case HttpError = 'http-error';
    case DigestMismatch = 'digest-mismatch';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
