<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum TaskClientStatus: string
{
    case Running = 'running';
    case Idle = 'idle';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Orphaned = 'orphaned';
}
