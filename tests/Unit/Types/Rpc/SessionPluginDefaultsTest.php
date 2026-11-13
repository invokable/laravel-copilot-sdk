<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionPluginDefaults;
use Revolution\Copilot\Types\Rpc\TeamPluginMarketplace;
use Revolution\Copilot\Types\Rpc\TeamPluginMarketplaceSource;

it('round trips plugin defaults and nested Team marketplace definitions', function () {
    $data = [
        'generation' => 'generation-1',
        'defaultEnabledPlugins' => ['team/plugin@marketplace' => true],
        'teamDefaultEnabledPlugins' => ['team/plugin@marketplace' => false],
        'extraKnownMarketplaces' => [
            'team-marketplace' => [
                'source' => [
                    'source' => 'github',
                    'repo' => 'team/plugins',
                ],
            ],
        ],
    ];

    $defaults = SessionPluginDefaults::fromArray($data);

    expect($defaults->extraKnownMarketplaces['team-marketplace'])
        ->toBeInstanceOf(TeamPluginMarketplace::class)
        ->and($defaults->extraKnownMarketplaces['team-marketplace']->source)
        ->toBeInstanceOf(TeamPluginMarketplaceSource::class)
        ->and($defaults->toArray())->toBe($data);
});
