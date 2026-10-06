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
