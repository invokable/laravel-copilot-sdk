<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Immediate request status for an Auto tier switch.
 *
 * Whether the requested preference was already effective or was accepted for
 * later transactional activation.
 */
enum ModelSwitchAutoTierStatus: string
{
    /** Accepted but not committed. */
    case PENDING = 'pending';

    /** The requested preference was already effective. */
    case UNCHANGED = 'unchanged';
}
