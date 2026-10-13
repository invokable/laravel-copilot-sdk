<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum TaskClientUpdateKind: string
{
    case Progress = 'progress';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
