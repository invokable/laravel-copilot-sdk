<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionDecisionOutcome;

describe('PermissionDecisionOutcome', function () {
    it('has correct string values', function () {
        expect(PermissionDecisionOutcome::AUTO_APPROVED->value)->toBe('auto_approved')
            ->and(PermissionDecisionOutcome::AUTOPILOT_DENIED->value)->toBe('autopilot_denied')
            ->and(PermissionDecisionOutcome::PROMPTED_USER->value)->toBe('prompted_user');
    });

    it('can be created from string', function () {
        expect(PermissionDecisionOutcome::from('auto_approved'))->toBe(PermissionDecisionOutcome::AUTO_APPROVED)
            ->and(PermissionDecisionOutcome::from('autopilot_denied'))->toBe(PermissionDecisionOutcome::AUTOPILOT_DENIED)
            ->and(PermissionDecisionOutcome::from('prompted_user'))->toBe(PermissionDecisionOutcome::PROMPTED_USER);
    });
});
