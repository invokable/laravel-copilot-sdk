<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ManagedPermissionVerdict: string
{
    case DENY = 'deny';
    case ASK = 'ask';
    case ALLOW = 'allow';
    case UNMANAGED = 'unmanaged';
}
