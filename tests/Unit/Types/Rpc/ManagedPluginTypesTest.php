<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ManagedPluginProgressPhase;
use Revolution\Copilot\Types\Rpc\ManagedPluginProgressData;
use Revolution\Copilot\Types\Rpc\SessionPluginsRetryManagedResult;

it('round trips managed plugin retry results and progress phases', function () {
    $result = SessionPluginsRetryManagedResult::fromArray([
        'plugins' => [
            ['spec' => 'org/plugin@marketplace', 'status' => 'installed'],
            ['spec' => 'org/other@marketplace', 'status' => 'failed', 'error' => 'Unavailable'],
        ],
    ]);
    $progress = ManagedPluginProgressData::fromArray([
        'phase' => 'installing',
        'pluginSpecs' => ['org/plugin@marketplace'],
    ]);

    expect($result->plugins[0]->status->value)->toBe('installed')
        ->and($result->plugins[1]->error)->toBe('Unavailable')
        ->and($progress->phase)->toBe(ManagedPluginProgressPhase::INSTALLING)
        ->and($progress->toArray()['pluginSpecs'])->toBe(['org/plugin@marketplace']);
});
