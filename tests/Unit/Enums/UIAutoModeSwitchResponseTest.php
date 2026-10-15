<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\UIAutoModeSwitchResponse;

describe('UIAutoModeSwitchResponse', function () {
    it('contains the supported responses', function () {
        expect(UIAutoModeSwitchResponse::Yes->value)->toBe('yes')
            ->and(UIAutoModeSwitchResponse::YesAlways->value)->toBe('yes_always')
            ->and(UIAutoModeSwitchResponse::No->value)->toBe('no')
            ->and(UIAutoModeSwitchResponse::tryFrom('invalid'))->toBeNull();
    });
});
