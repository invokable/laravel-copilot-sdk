<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionLimitsExhaustedResponseAction;

describe('SessionLimitsExhaustedResponseAction', function () {
    it('has all expected cases', function () {
        expect(SessionLimitsExhaustedResponseAction::Add->value)->toBe('add')
            ->and(SessionLimitsExhaustedResponseAction::Set->value)->toBe('set')
            ->and(SessionLimitsExhaustedResponseAction::Unset->value)->toBe('unset')
            ->and(SessionLimitsExhaustedResponseAction::Cancel->value)->toBe('cancel');
    });

    it('can create from string', function () {
        expect(SessionLimitsExhaustedResponseAction::from('add'))->toBe(SessionLimitsExhaustedResponseAction::Add)
            ->and(SessionLimitsExhaustedResponseAction::from('set'))->toBe(SessionLimitsExhaustedResponseAction::Set)
            ->and(SessionLimitsExhaustedResponseAction::from('unset'))->toBe(SessionLimitsExhaustedResponseAction::Unset)
            ->and(SessionLimitsExhaustedResponseAction::from('cancel'))->toBe(SessionLimitsExhaustedResponseAction::Cancel);
    });

    it('returns null for unknown value', function () {
        expect(SessionLimitsExhaustedResponseAction::tryFrom('unknown'))->toBeNull();
    });
});
