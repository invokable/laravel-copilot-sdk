<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFsErrorCode;

describe('SessionFsErrorCode', function () {
    it('has correct string values', function () {
        expect(SessionFsErrorCode::ENOENT->value)->toBe('ENOENT')
            ->and(SessionFsErrorCode::UNKNOWN->value)->toBe('UNKNOWN');
    });

    it('can be created from string', function () {
        expect(SessionFsErrorCode::from('ENOENT'))->toBe(SessionFsErrorCode::ENOENT)
            ->and(SessionFsErrorCode::from('UNKNOWN'))->toBe(SessionFsErrorCode::UNKNOWN);
    });

    it('has all expected cases', function () {
        expect(SessionFsErrorCode::cases())->toHaveCount(2);
    });
});
