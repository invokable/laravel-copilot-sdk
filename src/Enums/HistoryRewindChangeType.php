<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Aggregate file change represented by a rewind preview.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum HistoryRewindChangeType: string
{
    case CREATED = 'created';
    case DELETED = 'deleted';
    case MODIFIED = 'modified';
}
