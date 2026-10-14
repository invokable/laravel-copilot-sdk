<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogAgentPluginInstallability;
use Revolution\Copilot\Enums\CatalogAgentPluginMediaType;
use Revolution\Copilot\Types\Rpc\CatalogAgentPluginCandidate;
use Revolution\Copilot\Types\Rpc\CatalogAgentPluginInstallPlan;
use Revolution\Copilot\Types\Rpc\CatalogSearchSucceeded;

describe('CatalogAgentPluginCandidate', function () {
    it('round-trips the plugin candidate and its optional install plan', function () {
        $data = [
            'kind' => 'plugin',
            'mediaType' => 'application/vnd.anthropic.claude-plugin+json',
            'identity' => 'urn:github:copilot:plugin:example',
            'displayName' => 'Example Plugin',
            'source' => [
                'repository' => 'example/plugin',
                'path' => 'plugins/example',
            ],
            'compatibilityTags' => ['canvas', 'github-copilot'],
            'provenance' => [
                'authority' => 'github.com',
                'observedAt' => '2025-01-01T00:00:00Z',
                'mediaType' => 'application/vnd.anthropic.claude-plugin+json',
            ],
            'version' => '1.2.3',
            'description' => 'A plugin for review.',
            'publisher' => 'Example Publisher',
            'installability' => 'installable',
            'installPlan' => [
                'sources' => [
                    ['kind' => 'materialization'],
                    ['kind' => 'marketplace', 'name' => 'example', 'repository' => 'example/marketplace'],
                    ['kind' => 'repository', 'repository' => 'example/plugin', 'path' => 'plugins/example'],
                ],
                'pluginName' => 'example-plugin',
                'version' => '1.2.3',
                'commit' => '0123456789abcdef0123456789abcdef01234567',
                'components' => ['agent', 'canvas'],
                'canvasIds' => ['example-canvas'],
                'extensionIds' => ['example-extension'],
                'mcpDependencies' => ['example-server'],
                'connectorDependencies' => ['example-connector'],
            ],
            'trust' => [
                'schemaVersion' => 'v1',
                'status' => 'current',
                'tier' => 'T1',
                'eligibility' => 'default',
                'provenance' => [
                    'source' => 'agent-finder',
                    'observedAt' => '2024-01-01T00:00:00Z',
                ],
            ],
        ];

        $candidate = CatalogAgentPluginCandidate::fromArray($data);

        expect($candidate->kind)->toBe('plugin')
            ->and($candidate->mediaType)->toBe(CatalogAgentPluginMediaType::AnthropicClaudePluginJson)
            ->and($candidate->installability)->toBe(CatalogAgentPluginInstallability::Installable)
            ->and($candidate->installPlan)->toBeInstanceOf(CatalogAgentPluginInstallPlan::class)
            ->and($candidate->toArray())->toBe($data);
    });

    it('leaves capability-gated fields absent when they are not supplied', function () {
        $data = [
            'kind' => 'plugin',
            'mediaType' => 'application/vnd.github.copilot-plugin',
            'identity' => 'urn:github:copilot:plugin:example',
            'displayName' => 'Example Plugin',
            'source' => [
                'repository' => 'example/plugin',
                'path' => 'plugins/example',
            ],
            'compatibilityTags' => [],
            'provenance' => [
                'authority' => 'github.com',
                'observedAt' => '2025-01-01T00:00:00Z',
                'mediaType' => 'application/vnd.github.copilot-plugin',
            ],
        ];

        $candidate = CatalogAgentPluginCandidate::fromArray($data);

        expect($candidate->version)->toBeNull()
            ->and($candidate->description)->toBeNull()
            ->and($candidate->publisher)->toBeNull()
            ->and($candidate->installability)->toBeNull()
            ->and($candidate->installPlan)->toBeNull()
            ->and($candidate->trust)->toBeNull()
            ->and($candidate->toArray())->toBe($data);
    });
});

it('maps plugin candidates in catalog search results', function () {
    $result = CatalogSearchSucceeded::fromArray([
        'searchId' => 'search-1',
        'candidates' => [
            [
                'kind' => 'plugin',
                'mediaType' => 'application/agent-plugin+json',
                'identity' => 'urn:github:copilot:plugin:example',
                'displayName' => 'Example Plugin',
                'source' => [
                    'repository' => 'example/plugin',
                    'path' => 'plugins/example',
                ],
                'compatibilityTags' => [],
                'provenance' => [
                    'authority' => 'github.com',
                    'observedAt' => '2025-01-01T00:00:00Z',
                    'mediaType' => 'application/agent-plugin+json',
                ],
            ],
        ],
        'truncated' => false,
        'negotiated' => ['runtimeProtocolVersion' => 1, 'grantedCapabilities' => []],
    ]);

    expect($result->candidates[0])->toBeInstanceOf(CatalogAgentPluginCandidate::class)
        ->and($result->toArray()['candidates'][0]['kind'])->toBe('plugin');
});
