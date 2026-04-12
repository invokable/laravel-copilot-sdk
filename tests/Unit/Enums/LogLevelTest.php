<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\LogLevel;

describe('LogLevel', function () {
    it('has correct string values', function () {
        expect(LogLevel::INFO->value)->toBe('info')
            ->and(LogLevel::WARNING->value)->toBe('warning')
            ->and(LogLevel::ERROR->value)->toBe('error');
    });

    it('can be created from string', function () {
        expect(LogLevel::from('info'))->toBe(LogLevel::INFO)
            ->and(LogLevel::from('warning'))->toBe(LogLevel::WARNING)
            ->and(LogLevel::from('error'))->toBe(LogLevel::ERROR);
    });

    it('has all expected cases', function () {
        expect(LogLevel::cases())->toHaveCount(3);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(LogLevel::tryFrom('debug'))->toBeNull();
    });
});
