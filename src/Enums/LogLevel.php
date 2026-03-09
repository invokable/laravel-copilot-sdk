<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Valid log severity levels for session timeline messages.
 *
 * Determines how the message is displayed in the session timeline.
 */
enum LogLevel: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
}
