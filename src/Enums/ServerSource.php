<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Configuration source for discovered MCP servers.
 */
enum ServerSource: string
{
    case BUILTIN = 'builtin';
    case PLUGIN = 'plugin';
    case USER = 'user';
    case WORKSPACE = 'workspace';
}
