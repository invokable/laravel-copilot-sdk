<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\SessionEventType;

describe('AutoTier', function () {
    it('has correct values including the new fast tier', function () {
        expect(AutoTier::EFFICIENCY->value)->toBe('efficiency')
            ->and(AutoTier::BALANCE->value)->toBe('balance')
            ->and(AutoTier::INTELLIGENCE->value)->toBe('intelligence')
            ->and(AutoTier::FAST->value)->toBe('fast');
    });
});

describe('SessionEventType auto tier recommendation', function () {
    it('has the auto tier recommendation event', function () {
        expect(SessionEventType::SESSION_AUTO_TIER_RECOMMENDATION->value)->toBe('session.auto_tier_recommendation');
    });
});
