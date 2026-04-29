<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Current lifecycle status of a task.
 */
enum TaskStatus: string
{
    case Running = 'running';
    case Idle = 'idle';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
