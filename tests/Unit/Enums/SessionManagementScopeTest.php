<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionManagementScope;

describe('SessionManagementScope', function () {
    it('defines the supported peer boundaries', function () {
        expect(SessionManagementScope::All->value)->toBe('all')
            ->and(SessionManagementScope::InProcess->value)->toBe('in-process');
    });
});
