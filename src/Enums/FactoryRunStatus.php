<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Current or terminal state of a factory run.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum FactoryRunStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case HALTED = 'halted';
    case CANCELLED = 'cancelled';
    case ERROR = 'error';
}
