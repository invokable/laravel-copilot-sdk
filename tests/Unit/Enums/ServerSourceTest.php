<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ServerSource;

describe('ServerSource', function () {
    it('has all expected cases', function () {
        expect(ServerSource::cases())->toHaveCount(4);
    });

    it('has correct values', function () {
        expect(ServerSource::BUILTIN->value)->toBe('builtin')
            ->and(ServerSource::PLUGIN->value)->toBe('plugin')
            ->and(ServerSource::USER->value)->toBe('user')
            ->and(ServerSource::WORKSPACE->value)->toBe('workspace');
    });

    it('can be created from string', function () {
        expect(ServerSource::from('builtin'))->toBe(ServerSource::BUILTIN)
            ->and(ServerSource::from('plugin'))->toBe(ServerSource::PLUGIN)
            ->and(ServerSource::from('user'))->toBe(ServerSource::USER)
            ->and(ServerSource::from('workspace'))->toBe(ServerSource::WORKSPACE);
    });

    it('returns null for unknown values via tryFrom', function () {
        expect(ServerSource::tryFrom('unknown'))->toBeNull();
    });
});
