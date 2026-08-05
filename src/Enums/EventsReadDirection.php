<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Direction used when paging through the persisted session event log.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum EventsReadDirection: string
{
    case FORWARD = 'forward';
    case BACKWARD = 'backward';
}
