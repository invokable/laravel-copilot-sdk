<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * A wire feature a caller can require of the catalog surface, negotiated per request.
 *
 * @experimental
 */
enum CatalogCapability: string
{
    /** Understands the current `application/mcp-server-card+json` media type. */
    case McpServerCard = 'mcp-server-card';

    /** Understands the legacy `application/mcp-server+json` media type. */
    case LegacyMcpServerCard = 'legacy-mcp-server-card';

    /** Understands `application/ai-skill` candidates as discovery-only and typed non-installable. */
    case AiSkillDiscovery = 'ai-skill-discovery';

    /** Understands side-effect-free MCP install-plan requests, results, and plan handles. */
    case McpInstallPlanning = 'mcp-install-planning';

    /** Understands plans that enumerate every eligible transport rather than a single preferred one. */
    case MultipleTransportChoice = 'multiple-transport-choice';
}
