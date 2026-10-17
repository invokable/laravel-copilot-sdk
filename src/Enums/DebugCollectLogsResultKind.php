<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Destination type used for a collected debug log bundle.
 *
 * @experimental
 */
enum DebugCollectLogsResultKind: string
{
    case Archive = 'archive';
    case Directory = 'directory';
}
