<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CanvasInstanceAvailability;

describe('CanvasInstanceAvailability', function () {
    it('has correct string values', function () {
        expect(CanvasInstanceAvailability::READY->value)->toBe('ready')
            ->and(CanvasInstanceAvailability::STALE->value)->toBe('stale');
    });

    it('can be created from string', function () {
        expect(CanvasInstanceAvailability::from('ready'))->toBe(CanvasInstanceAvailability::READY)
            ->and(CanvasInstanceAvailability::from('stale'))->toBe(CanvasInstanceAvailability::STALE);
    });

    it('has all expected cases', function () {
        $cases = CanvasInstanceAvailability::cases();

        expect($cases)->toHaveCount(2)
            ->and($cases)->toContain(CanvasInstanceAvailability::READY)
            ->and($cases)->toContain(CanvasInstanceAvailability::STALE);
    });
});
