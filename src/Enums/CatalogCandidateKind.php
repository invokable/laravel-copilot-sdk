<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * What kind of resource a catalog candidate describes.
 *
 * @experimental
 */
enum CatalogCandidateKind: string
{
    /** An MCP server, which can be planned for installation. */
    case McpServer = 'mcp-server';

    /** An AI skill, which is discoverable but not installable through this surface. */
    case AiSkill = 'ai-skill';
}
