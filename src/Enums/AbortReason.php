<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Finite reason code describing why the current turn was aborted.
 */
enum AbortReason: string
{
    case UserInitiated = 'user_initiated';
    case RemoteCommand = 'remote_command';
    case UserAbort = 'user_abort';
}
