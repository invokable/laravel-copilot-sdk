<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * MCP server transport type.
 *
 * Local configs are normalized to "stdio" by the runtime.
 */
enum McpTransportType: string
{
    case STDIO = 'stdio';
    case HTTP = 'http';
    case SSE = 'sse';
    case MEMORY = 'memory';
}
