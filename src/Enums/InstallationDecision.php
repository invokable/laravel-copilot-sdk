<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Explicit human decision for a reviewed installation operation.
 *
 * @experimental
 */
enum InstallationDecision: string
{
    case CONFIRM = 'confirm';
    case DECLINE = 'decline';
    case CANCEL = 'cancel';
}
