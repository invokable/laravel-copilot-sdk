<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

describe('session provider configuration', function () {
    it('keeps session callbacks local while preserving them in SessionConfig', function () {
        $providers = sessionProviderCallbacks();
        $config = new SessionConfig(
            skillProvider: $providers['skill'],
            sessionFsProvider: $providers['filesystem'],
        );

        expect(array_key_exists('skillProvider', $config->toArray()))->toBeFalse()
            ->and(array_key_exists('sessionFsProvider', $config->toArray()))->toBeFalse()
            ->and(SessionConfig::fromArray([
                'skillProvider' => $providers['skill'],
                'sessionFsProvider' => $providers['filesystem'],
            ])->skillProvider)->toBe($providers['skill'])
            ->and(SessionConfig::fromArray([
                'skillProvider' => $providers['skill'],
                'sessionFsProvider' => $providers['filesystem'],
            ])->sessionFsProvider)->toBe($providers['filesystem']);
    });

    it('supports ephemeral callbacks when resuming without serializing them', function () {
        $providers = sessionProviderCallbacks();
        $config = new ResumeSessionConfig(
            skillProvider: $providers['skill'],
            sessionFsProvider: $providers['filesystem'],
        );

        expect(array_key_exists('skillProvider', $config->toArray()))->toBeFalse()
            ->and(array_key_exists('sessionFsProvider', $config->toArray()))->toBeFalse()
            ->and(ResumeSessionConfig::fromArray([
                'skillProvider' => $providers['skill'],
                'sessionFsProvider' => $providers['filesystem'],
            ])->skillProvider)->toBe($providers['skill'])
            ->and(ResumeSessionConfig::fromArray([
                'skillProvider' => $providers['skill'],
                'sessionFsProvider' => $providers['filesystem'],
            ])->sessionFsProvider)->toBe($providers['filesystem']);
    });
});

describe('createSessionFsProvider configuration', function () {
    it('round-trips the factory through fromArray and toArray for SessionConfig', function () {
        $factory = fn () => sessionProviderCallbacks()['filesystem'];

        $config = SessionConfig::fromArray(['createSessionFsProvider' => $factory]);

        expect($config->createSessionFsProvider)->toBe($factory)
            ->and($config->toArray()['createSessionFsProvider'])->toBe($factory)
            ->and((new SessionConfig(createSessionFsProvider: $factory))->createSessionFsProvider)->toBe($factory)
            ->and(SessionConfig::fromArray([])->createSessionFsProvider)->toBeNull()
            ->and(array_key_exists('createSessionFsProvider', (new SessionConfig)->toArray()))->toBeFalse();
    });

    it('round-trips the factory through fromArray and toArray for ResumeSessionConfig', function () {
        $factory = fn () => sessionProviderCallbacks()['filesystem'];

        $config = ResumeSessionConfig::fromArray(['createSessionFsProvider' => $factory]);

        expect($config->createSessionFsProvider)->toBe($factory)
            ->and($config->toArray()['createSessionFsProvider'])->toBe($factory)
            ->and(ResumeSessionConfig::fromArray([])->createSessionFsProvider)->toBeNull()
            ->and(array_key_exists('createSessionFsProvider', (new ResumeSessionConfig)->toArray()))->toBeFalse();
    });
});

/**
 * @return array{
 *     skill: array{listSkills: Closure, readSkill: Closure},
 *     filesystem: array{readFile: Closure, writeFile: Closure}
 * }
 */
function sessionProviderCallbacks(): array
{
    return [
        'skill' => [
            'listSkills' => fn () => [],
            'readSkill' => fn () => null,
        ],
        'filesystem' => [
            'readFile' => fn () => '',
            'writeFile' => fn () => null,
        ],
    ];
}
