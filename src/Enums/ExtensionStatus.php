<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Extension runtime status.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum ExtensionStatus: string
{
    case RUNNING = 'running';
    case DISABLED = 'disabled';
    case FAILED = 'failed';
    case STARTING = 'starting';
}
