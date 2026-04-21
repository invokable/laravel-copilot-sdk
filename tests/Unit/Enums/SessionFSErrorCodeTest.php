<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFSErrorCode;

describe('SessionFSErrorCode', function () {
    it('has correct string values', function () {
        expect(SessionFSErrorCode::ENOENT->value)->toBe('ENOENT')
            ->and(SessionFSErrorCode::UNKNOWN->value)->toBe('UNKNOWN');
    });

    it('can be created from string', function () {
        expect(SessionFSErrorCode::from('ENOENT'))->toBe(SessionFSErrorCode::ENOENT)
            ->and(SessionFSErrorCode::from('UNKNOWN'))->toBe(SessionFSErrorCode::UNKNOWN);
    });

    it('has all expected cases', function () {
        expect(SessionFSErrorCode::cases())->toHaveCount(2);
    });
});
