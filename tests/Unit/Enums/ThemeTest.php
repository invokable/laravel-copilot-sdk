<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\Theme;

describe('Theme', function () {
    it('has correct string values', function () {
        expect(Theme::DARK->value)->toBe('dark')
            ->and(Theme::LIGHT->value)->toBe('light');
    });

    it('can be created from string', function () {
        expect(Theme::from('dark'))->toBe(Theme::DARK)
            ->and(Theme::from('light'))->toBe(Theme::LIGHT);
    });

    it('has all expected cases', function () {
        $cases = Theme::cases();

        expect($cases)->toHaveCount(2)
            ->and($cases)->toContain(Theme::DARK)
            ->and($cases)->toContain(Theme::LIGHT);
    });
});
