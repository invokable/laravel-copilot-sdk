<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ExtensionStatus;

describe('ExtensionStatus', function () {
    it('has correct string values', function () {
        expect(ExtensionStatus::RUNNING->value)->toBe('running')
            ->and(ExtensionStatus::DISABLED->value)->toBe('disabled')
            ->and(ExtensionStatus::FAILED->value)->toBe('failed')
            ->and(ExtensionStatus::STARTING->value)->toBe('starting');
    });

    it('can be created from string', function () {
        expect(ExtensionStatus::from('running'))->toBe(ExtensionStatus::RUNNING)
            ->and(ExtensionStatus::from('disabled'))->toBe(ExtensionStatus::DISABLED)
            ->and(ExtensionStatus::from('failed'))->toBe(ExtensionStatus::FAILED)
            ->and(ExtensionStatus::from('starting'))->toBe(ExtensionStatus::STARTING);
    });

    it('has all expected cases', function () {
        expect(ExtensionStatus::cases())->toHaveCount(4);
    });
});
