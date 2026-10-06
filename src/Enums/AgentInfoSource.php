<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Origin of an agent definition returned by the runtime.
 */
enum AgentInfoSource: string
{
    case BUILTIN = 'builtin';
    case INHERITED = 'inherited';
    case PLUGIN = 'plugin';
    case PROJECT = 'project';
    case REMOTE = 'remote';
    case USER = 'user';
}
