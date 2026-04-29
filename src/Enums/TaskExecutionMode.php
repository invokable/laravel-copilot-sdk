<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * How the agent/shell task is currently being managed by the runtime.
 */
enum TaskExecutionMode: string
{
    case Sync = 'sync';
    case Background = 'background';
}
