<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Execution outcome classification for a session tool result.
 *
 * @experimental
 */
enum ToolResultType: string
{
    case Success = 'success';
    case Failure = 'failure';
    case Timeout = 'timeout';
    case Rejected = 'rejected';
    case Denied = 'denied';
}
