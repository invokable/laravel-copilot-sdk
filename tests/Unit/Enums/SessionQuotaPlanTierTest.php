<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionQuotaPlanTier;

describe('SessionQuotaPlanTier', function () {
    it('has correct string values', function () {
        expect(SessionQuotaPlanTier::ENTERPRISE->value)->toBe('enterprise')
            ->and(SessionQuotaPlanTier::ACCESS->value)->toBe('access')
            ->and(SessionQuotaPlanTier::MAX->value)->toBe('max');
    });

    it('can be created from string', function () {
        expect(SessionQuotaPlanTier::from('access'))->toBe(SessionQuotaPlanTier::ACCESS)
            ->and(SessionQuotaPlanTier::tryFrom('access'))->toBe(SessionQuotaPlanTier::ACCESS);
    });

    it('has all expected cases', function () {
        $cases = SessionQuotaPlanTier::cases();

        expect($cases)->toHaveCount(9)
            ->and($cases)->toContain(SessionQuotaPlanTier::ACCESS);
    });
});
