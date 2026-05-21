<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Filter scope for session event log reads.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum EventsAgentScope: string
{
    case PRIMARY = 'primary';
    case ALL = 'all';
}
