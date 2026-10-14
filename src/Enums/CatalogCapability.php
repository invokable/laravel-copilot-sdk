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

    /** Understands Agent Plugin catalog candidates. */
    case AgentPluginDiscovery = 'agent-plugin-discovery';

    /** Understands Agent Plugin formats beyond `application/vnd.github.copilot-plugin`. */
    case AgentPluginFormats = 'agent-plugin-formats';

    /** Understands side-effect-free MCP install-plan requests, results, and plan handles. */
    case McpInstallPlanning = 'mcp-install-planning';

    /** Understands plans that enumerate every eligible transport rather than a single preferred one. */
    case MultipleTransportChoice = 'multiple-transport-choice';

    /** Understands versioned candidate trust snapshots. Protocol-3 callers must require this capability before the runtime adds the optional snapshot field. */
    case TrustSnapshot = 'trust-snapshot';

    /** Understands reviewable Agent Plugin installability and install-plan fields. */
    case AgentPluginConfirmedInstallation = 'agent-plugin-confirmed-installation';
}
