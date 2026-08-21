<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Media type a catalog card is interpreted as.
 *
 * @experimental
 */
enum CatalogMediaType: string
{
    /** The current MCP server card media type. */
    case McpServerCard = 'application/mcp-server-card+json';

    /** The legacy MCP server card media type, accepted for compatibility. */
    case LegacyMcpServerCard = 'application/mcp-server+json';

    /** An AI skill card. Representable and searchable, but typed non-installable. */
    case AiSkill = 'application/ai-skill';
}
