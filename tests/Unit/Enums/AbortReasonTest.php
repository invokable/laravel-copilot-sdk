<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AbortReason;

describe('AbortReason', function () {
    it('has the expected cases', function () {
        expect(AbortReason::UserInitiated->value)->toBe('user_initiated')
            ->and(AbortReason::RemoteCommand->value)->toBe('remote_command')
            ->and(AbortReason::UserAbort->value)->toBe('user_abort');
    });

    it('can be resolved from string value', function () {
        expect(AbortReason::from('user_initiated'))->toBe(AbortReason::UserInitiated)
            ->and(AbortReason::from('remote_command'))->toBe(AbortReason::RemoteCommand)
            ->and(AbortReason::from('user_abort'))->toBe(AbortReason::UserAbort);
    });

    it('returns null for unknown values with tryFrom', function () {
        expect(AbortReason::tryFrom('unknown'))->toBeNull();
    });
});
