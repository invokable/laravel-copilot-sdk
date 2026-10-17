<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Source of an entry included in a debug log bundle.
 *
 * @experimental
 */
enum DebugCollectLogsSource: string
{
    case Events = 'events';
    case ProcessLog = 'process-log';
    case ShellLog = 'shell-log';
    case Additional = 'additional';
}
