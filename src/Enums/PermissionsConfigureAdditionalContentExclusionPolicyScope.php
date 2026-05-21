<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Scope of additional content-exclusion policy rules.
 */
enum PermissionsConfigureAdditionalContentExclusionPolicyScope: string
{
    case ALL = 'all';
    case REPO = 'repo';
}
