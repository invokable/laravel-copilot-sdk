<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogAgentPluginCompatibilityTag;
use Revolution\Copilot\Enums\CatalogAgentPluginComponent;
use Revolution\Copilot\Enums\CatalogAgentPluginInstallability;
use Revolution\Copilot\Enums\CatalogAgentPluginMediaType;
use Revolution\Copilot\Enums\CatalogCandidateKind;
use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogMediaType;

it('exposes the Agent Plugin media types', function () {
    expect(array_map(fn (CatalogAgentPluginMediaType $case): string => $case->value, CatalogAgentPluginMediaType::cases()))
        ->toBe([
            'application/vnd.github.copilot-plugin',
            'application/vnd.anthropic.claude-plugin+json',
            'application/agent-plugin+json',
        ]);

    expect(CatalogMediaType::from('application/vnd.github.copilot-plugin'))
        ->toBe(CatalogMediaType::GitHubCopilotPlugin)
        ->and(CatalogMediaType::from('application/vnd.anthropic.claude-plugin+json'))
        ->toBe(CatalogMediaType::AnthropicClaudePluginJson)
        ->and(CatalogMediaType::from('application/agent-plugin+json'))
        ->toBe(CatalogMediaType::AgentPluginJson);
});

it('exposes the Agent Plugin catalog capabilities and candidate kind', function () {
    expect(CatalogCandidateKind::Plugin->value)->toBe('plugin')
        ->and(CatalogCapability::AgentPluginDiscovery->value)->toBe('agent-plugin-discovery')
        ->and(CatalogCapability::AgentPluginFormats->value)->toBe('agent-plugin-formats')
        ->and(CatalogCapability::AgentPluginConfirmedInstallation->value)->toBe('agent-plugin-confirmed-installation');
});

it('exposes the Agent Plugin installability states', function () {
    expect(array_map(fn (CatalogAgentPluginInstallability $case): string => $case->value, CatalogAgentPluginInstallability::cases()))
        ->toBe([
            'installable',
            'install-data-unavailable',
            'feature-disabled',
            'not-installable-kind',
        ]);
});

it('exposes the Agent Plugin components and compatibility tags', function () {
    expect(array_map(fn (CatalogAgentPluginComponent $case): string => $case->value, CatalogAgentPluginComponent::cases()))
        ->toBe([
            'agent',
            'command',
            'skill',
            'hook',
            'mcp-server',
            'lsp-server',
            'extension',
            'canvas',
        ])
        ->and(array_map(fn (CatalogAgentPluginCompatibilityTag $case): string => $case->value, CatalogAgentPluginCompatibilityTag::cases()))
        ->toBe(['canvas', 'canvas-only', 'github-copilot']);
});
