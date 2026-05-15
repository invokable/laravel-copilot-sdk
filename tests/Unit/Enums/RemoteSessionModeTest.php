<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\RemoteSessionMode;

describe('RemoteSessionMode', function () {
    it('has all expected cases', function () {
        expect(RemoteSessionMode::Off->value)->toBe('off')
            ->and(RemoteSessionMode::Export->value)->toBe('export')
            ->and(RemoteSessionMode::On->value)->toBe('on');
    });

    it('can be created from string', function () {
        expect(RemoteSessionMode::from('off'))->toBe(RemoteSessionMode::Off)
            ->and(RemoteSessionMode::from('export'))->toBe(RemoteSessionMode::Export)
            ->and(RemoteSessionMode::from('on'))->toBe(RemoteSessionMode::On);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(RemoteSessionMode::tryFrom('invalid'))->toBeNull();
    });
});
