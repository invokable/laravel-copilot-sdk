<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\HookOrigin;
use Revolution\Copilot\Enums\HookType;
use Revolution\Copilot\Types\Rpc\DiscoveredHook;
use Revolution\Copilot\Types\Rpc\HooksDiscoverRequest;
use Revolution\Copilot\Types\Rpc\HooksDiscoverResult;
use Revolution\Copilot\Types\Rpc\SandboxHostCapability;
use Revolution\Copilot\Types\Rpc\SandboxHostSupport;

it('converts optional hook discovery request parameters', function () {
    expect(HooksDiscoverRequest::fromArray([])->toArray())->toBe([])
        ->and(HooksDiscoverRequest::fromArray([
            'hookDirectories' => [],
            'projectPaths' => ['/workspace'],
            'excludeHostHooks' => false,
        ])->toArray())->toBe([
            'hookDirectories' => [],
            'projectPaths' => ['/workspace'],
            'excludeHostHooks' => false,
        ]);
});

it('round trips discovered hooks and hook discovery results', function () {
    $result = HooksDiscoverResult::fromArray([
        'hooks' => [[
            'id' => 'hook-1',
            'hookType' => 'preToolUse',
            'origin' => 'repository',
            'source' => '.github/hooks.json',
            'projectPath' => '/workspace',
            'enabled' => false,
        ]],
        'warnings' => ['warning'],
        'errors' => ['error'],
    ]);

    expect($result->hooks[0])->toBeInstanceOf(DiscoveredHook::class)
        ->and($result->hooks[0]->hookType)->toBe(HookType::PreToolUse)
        ->and($result->hooks[0]->origin)->toBe(HookOrigin::Repository)
        ->and($result->hooks[0]->enabled)->toBeFalse()
        ->and($result->toArray())->toBe([
            'hooks' => [[
                'id' => 'hook-1',
                'hookType' => 'preToolUse',
                'origin' => 'repository',
                'source' => '.github/hooks.json',
                'projectPath' => '/workspace',
                'enabled' => false,
            ]],
            'warnings' => ['warning'],
            'errors' => ['error'],
        ]);
});

it('defaults absent hook discovery result fields', function () {
    expect(HooksDiscoverResult::fromArray([])->toArray())->toBe([
        'hooks' => [],
        'warnings' => [],
        'errors' => [],
    ])
        ->and(DiscoveredHook::fromArray([])->toArray())->toBe([
            'id' => '',
            'hookType' => '',
            'origin' => '',
            'source' => '',
            'enabled' => false,
        ]);
});

it('round trips sandbox host support and capabilities', function () {
    $support = SandboxHostSupport::fromArray([
        'supported' => true,
        'capabilities' => [
            ['name' => 'network', 'supported' => false, 'reason' => 'install helper'],
        ],
    ]);

    expect($support->capabilities[0])->toBeInstanceOf(SandboxHostCapability::class)
        ->and($support->toArray())->toBe([
            'supported' => true,
            'capabilities' => [
                ['name' => 'network', 'supported' => false, 'reason' => 'install helper'],
            ],
        ]);
});

it('defaults absent sandbox host support fields', function () {
    expect(SandboxHostSupport::fromArray([])->toArray())->toBe([
        'supported' => false,
        'capabilities' => [],
    ])
        ->and(SandboxHostCapability::fromArray([])->toArray())->toBe([
            'name' => '',
            'supported' => false,
        ]);
});
