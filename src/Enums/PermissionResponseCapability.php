<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Response capability available to the client when settling a permission request.
 */
enum PermissionResponseCapability: string
{
    case INTERACTIVE = 'interactive';
    case HEADLESS = 'headless';
    case NONE = 'none';
}
