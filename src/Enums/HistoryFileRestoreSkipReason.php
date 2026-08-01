<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Reason a captured file was not restored during a rewind.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum HistoryFileRestoreSkipReason: string
{
    case USER_MODIFIED = 'user-modified';
    case SKIPPED_CAPTURE = 'skipped-capture';
}
