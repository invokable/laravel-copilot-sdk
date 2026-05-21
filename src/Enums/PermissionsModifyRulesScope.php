<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Scope for rule modifications.
 */
enum PermissionsModifyRulesScope: string
{
    case LOCATION = 'location';
    case SESSION = 'session';
}
