<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ExtensionSource;

describe('ExtensionSource', function () {
    it('has correct string values', function () {
        expect(ExtensionSource::PROJECT->value)->toBe('project')
            ->and(ExtensionSource::USER->value)->toBe('user')
            ->and(ExtensionSource::PLUGIN->value)->toBe('plugin')
            ->and(ExtensionSource::SESSION->value)->toBe('session');
    });

    it('can be created from string', function () {
        expect(ExtensionSource::from('project'))->toBe(ExtensionSource::PROJECT)
            ->and(ExtensionSource::from('user'))->toBe(ExtensionSource::USER)
            ->and(ExtensionSource::from('plugin'))->toBe(ExtensionSource::PLUGIN)
            ->and(ExtensionSource::from('session'))->toBe(ExtensionSource::SESSION);
    });

    it('has all expected cases', function () {
        expect(ExtensionSource::cases())->toHaveCount(4);
    });
});
