<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental GitHub Mission Control compute kind. */
enum EnvironmentKind: string
{
    case MANAGED_ACTIONS = 'managed-actions';
    case MANAGED_CCA = 'managed-cca';
    case MANAGED_SANDBOX = 'managed-sandbox';
    case USER_CODESPACE = 'user-codespace';
    case USER_LOCAL = 'user-local';
}
