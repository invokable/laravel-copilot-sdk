<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * MCP server connection status.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum McpServerStatus: string
{
    case CONNECTED = 'connected';
    case FAILED = 'failed';
    case NEEDS_AUTH = 'needs-auth';
    case PENDING = 'pending';
    case DISABLED = 'disabled';
    case NOT_CONFIGURED = 'not_configured';
}
