<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Filesystem entry type for a debug log bundle entry.
 *
 * @experimental
 */
enum DebugCollectLogsEntryKind: string
{
    case File = 'file';
    case Directory = 'directory';
}
