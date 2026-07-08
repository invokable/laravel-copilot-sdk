<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Output verbosity level used for supported model calls.
 */
enum Verbosity: string
{
    /** A terse response was requested. */
    case LOW = 'low';
    /** A medium amount of response detail was requested. */
    case MEDIUM = 'medium';
    /** A more detailed response was requested. */
    case HIGH = 'high';
}
