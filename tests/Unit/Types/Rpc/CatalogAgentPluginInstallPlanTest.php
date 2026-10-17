<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogAgentPluginComponent;
use Revolution\Copilot\Types\Rpc\CatalogAgentPluginInstallPlan;
use Revolution\Copilot\Types\Rpc\CatalogAgentPluginMarketplaceSource;
use Revolution\Copilot\Types\Rpc\CatalogAgentPluginMaterializationSource;
use Revolution\Copilot\Types\Rpc\CatalogAgentPluginRepositorySource;

it('round-trips all reviewable Agent Plugin install sources', function () {
    $data = [
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
    ];

    $plan = CatalogAgentPluginInstallPlan::fromArray($data);

    expect($plan->sources[0])->toBeInstanceOf(CatalogAgentPluginMaterializationSource::class)
        ->and($plan->sources[1])->toBeInstanceOf(CatalogAgentPluginMarketplaceSource::class)
        ->and($plan->sources[2])->toBeInstanceOf(CatalogAgentPluginRepositorySource::class)
        ->and($plan->components)->toBe([CatalogAgentPluginComponent::Agent, CatalogAgentPluginComponent::Canvas])
        ->and($plan->toArray())->toBe($data);
});

it('omits optional plan version, commit, and repository path', function () {
    $data = [
        'sources' => [
            ['kind' => 'repository', 'repository' => 'example/plugin'],
        ],
        'pluginName' => 'example-plugin',
        'components' => [],
        'canvasIds' => [],
        'extensionIds' => [],
        'mcpDependencies' => [],
        'connectorDependencies' => [],
    ];

    $plan = CatalogAgentPluginInstallPlan::fromArray($data);

    expect($plan->version)->toBeNull()
        ->and($plan->commit)->toBeNull()
        ->and($plan->toArray())->toBe($data);
});

it('omits the plugin name from a materialisation-only plan', function () {
    $data = [
        'sources' => [
            ['kind' => 'materialization'],
        ],
        'components' => [],
        'canvasIds' => [],
        'extensionIds' => [],
        'mcpDependencies' => [],
        'connectorDependencies' => [],
    ];

    $plan = CatalogAgentPluginInstallPlan::fromArray($data);

    expect($plan->pluginName)->toBeNull()
        ->and($plan->sources[0])->toBeInstanceOf(CatalogAgentPluginMaterializationSource::class)
        ->and($plan->toArray())->toBe($data);
});
