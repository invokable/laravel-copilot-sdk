<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Sandbox state change reported by a slash command.
 *
 * @experimental
 */
enum SandboxSessionChange: string
{
    case DISABLED = 'disabled';
    case RESTORED = 'restored';
}
