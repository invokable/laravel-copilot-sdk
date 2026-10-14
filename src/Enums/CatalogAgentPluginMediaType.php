<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Media type naming the format of an Agent Plugin catalog candidate.
 *
 * @experimental
 */
enum CatalogAgentPluginMediaType: string
{
    case GitHubCopilotPlugin = 'application/vnd.github.copilot-plugin';

    case AnthropicClaudePluginJson = 'application/vnd.anthropic.claude-plugin+json';

    case AgentPluginJson = 'application/agent-plugin+json';
}
