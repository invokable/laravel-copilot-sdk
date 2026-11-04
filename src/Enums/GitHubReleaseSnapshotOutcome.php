<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubReleaseSnapshotOutcome: string
{
    case Success = 'success';
    case Failed = 'failed';
}
