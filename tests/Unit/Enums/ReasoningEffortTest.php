<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ReasoningEffort;

describe('ReasoningEffort', function () {
    it('has correct string values', function () {
        expect(ReasoningEffort::LOW->value)->toBe('low')
            ->and(ReasoningEffort::MEDIUM->value)->toBe('medium')
            ->and(ReasoningEffort::HIGH->value)->toBe('high')
            ->and(ReasoningEffort::XHIGH->value)->toBe('xhigh');
    });

    it('can be created from string', function () {
        expect(ReasoningEffort::from('low'))->toBe(ReasoningEffort::LOW)
            ->and(ReasoningEffort::from('medium'))->toBe(ReasoningEffort::MEDIUM)
            ->and(ReasoningEffort::from('high'))->toBe(ReasoningEffort::HIGH)
            ->and(ReasoningEffort::from('xhigh'))->toBe(ReasoningEffort::XHIGH);
    });

    it('has all expected cases', function () {
        $cases = ReasoningEffort::cases();

        expect($cases)->toHaveCount(4)
            ->and($cases)->toContain(ReasoningEffort::LOW)
            ->and($cases)->toContain(ReasoningEffort::MEDIUM)
            ->and($cases)->toContain(ReasoningEffort::HIGH)
            ->and($cases)->toContain(ReasoningEffort::XHIGH);
    });
});
