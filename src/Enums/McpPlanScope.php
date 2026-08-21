<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Configuration scope an MCP install plan targets.
 *
 * @experimental
 */
enum McpPlanScope: string
{
    /** The user's own MCP configuration. */
    case User = 'user';
}
