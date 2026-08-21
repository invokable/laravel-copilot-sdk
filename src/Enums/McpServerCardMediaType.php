<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * JSON MCP card media type accepted for install planning.
 *
 * @experimental
 */
enum McpServerCardMediaType: string
{
    /** The current MCP server card media type. */
    case McpServerCard = 'application/mcp-server-card+json';

    /** The legacy MCP server card media type, accepted for compatibility. */
    case LegacyMcpServerCard = 'application/mcp-server+json';
}
