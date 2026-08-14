<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Disposition of a permission request as observed by the responding client.
 */
enum PermissionDecisionOutcome: string
{
    /** The request was approved automatically without a new human decision. */
    case AUTO_APPROVED = 'auto_approved';

    /** The request was denied without an interactive user decision; source records why. */
    case AUTOPILOT_DENIED = 'autopilot_denied';

    /** The response came from an interactive user prompt. */
    case PROMPTED_USER = 'prompted_user';
}
