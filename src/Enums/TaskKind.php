<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Closed set of public task kinds a connection can negotiate.
 */
enum TaskKind: string
{
    case AGENT = 'agent';
    case CLIENT = 'client';
    case SHELL = 'shell';
}
