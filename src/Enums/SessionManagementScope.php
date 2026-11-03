<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Peer boundary used by local session management.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum SessionManagementScope: string
{
    case All = 'all';

    case InProcess = 'in-process';
}
