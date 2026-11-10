<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFeatureFlagsMode;

describe('SessionFeatureFlagsMode', function () {
    it('defines the supported session assignment sources', function () {
        expect(SessionFeatureFlagsMode::Local->value)->toBe('local')
            ->and(SessionFeatureFlagsMode::Cli->value)->toBe('cli');
    });
});
