<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Elicitation response action from the user.
 */
enum ElicitationAction: string
{
    case ACCEPT = 'accept';
    case DECLINE = 'decline';
    case CANCEL = 'cancel';
}
