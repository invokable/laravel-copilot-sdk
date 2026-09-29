<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Origin of a sandbox choice supplied by the host.
 *
 * @experimental
 */
enum SandboxConfigSource: string
{
    case NEVER_CONFIGURED = 'never_configured';
    case USER_ENABLED = 'user_enabled';
    case USER_DISABLED = 'user_disabled';
    case SESSION_FLAG = 'session_flag';
    case SESSION_DISABLED = 'session_disabled';
    case UNSUPPORTED_HOST = 'unsupported_host';
    case REPOSITORY_POLICY = 'repository_policy';
}
