<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Session filesystem error classification.
 */
enum SessionFSErrorCode: string
{
    case ENOENT = 'ENOENT';
    case UNKNOWN = 'UNKNOWN';
}
