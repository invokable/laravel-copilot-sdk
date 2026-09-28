<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\FactoryRunStatus;
use Revolution\Copilot\Enums\SessionEventType;

describe('AutoTier', function () {
    it('has correct values including the new fast tier', function () {
        expect(AutoTier::EFFICIENCY->value)->toBe('efficiency')
            ->and(AutoTier::BALANCE->value)->toBe('balance')
            ->and(AutoTier::INTELLIGENCE->value)->toBe('intelligence')
            ->and(AutoTier::FAST->value)->toBe('fast');
    });
});

describe('FactoryRunStatus', function () {
    it('has correct values including the new paused status', function () {
        expect(FactoryRunStatus::PENDING->value)->toBe('pending')
            ->and(FactoryRunStatus::RUNNING->value)->toBe('running')
            ->and(FactoryRunStatus::COMPLETED->value)->toBe('completed')
            ->and(FactoryRunStatus::HALTED->value)->toBe('halted')
            ->and(FactoryRunStatus::PAUSED->value)->toBe('paused')
            ->and(FactoryRunStatus::CANCELLED->value)->toBe('cancelled')
            ->and(FactoryRunStatus::ERROR->value)->toBe('error');
    });
});

describe('SessionEventType new cases', function () {
    it('has the auto tier recommendation event', function () {
        expect(SessionEventType::SESSION_AUTO_TIER_RECOMMENDATION->value)->toBe('session.auto_tier_recommendation');
    });
});
