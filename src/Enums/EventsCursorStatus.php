<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Cursor status returned by session event log reads.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum EventsCursorStatus: string
{
    case OK = 'ok';
    case EXPIRED = 'expired';
}
