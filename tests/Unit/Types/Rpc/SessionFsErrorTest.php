<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFsErrorCode;
use Revolution\Copilot\Types\Rpc\SessionFsError;

describe('SessionFsError', function () {
    it('can be created from array with a code and message', function () {
        $error = SessionFsError::fromArray([
            'code' => 'ENOENT',
            'message' => 'File not found',
        ]);

        expect($error->code)->toBe(SessionFsErrorCode::ENOENT)
            ->and($error->message)->toBe('File not found');
    });

    it('defaults an omitted message to null', function () {
        $error = SessionFsError::fromArray(['code' => 'UNKNOWN']);

        expect($error->message)->toBeNull();
    });

    it('converts the code and message to array values', function () {
        $error = new SessionFsError(
            code: SessionFsErrorCode::UNKNOWN,
            message: 'Unexpected filesystem error',
        );

        expect($error->toArray())->toBe([
            'code' => 'UNKNOWN',
            'message' => 'Unexpected filesystem error',
        ]);
    });

    it('omits a null message when converted to array', function () {
        $error = new SessionFsError(code: SessionFsErrorCode::ENOENT);

        expect($error->toArray())->toBe(['code' => 'ENOENT']);
    });

    it('keeps an empty message when converted to array', function () {
        $error = new SessionFsError(code: SessionFsErrorCode::ENOENT, message: '');

        expect($error->toArray())->toBe([
            'code' => 'ENOENT',
            'message' => '',
        ]);
    });

    it('preserves an unknown error code as a string', function () {
        $error = SessionFsError::fromArray(['code' => 'EUNKNOWN']);

        expect($error->code)->toBe('EUNKNOWN')
            ->and($error->toArray())->toBe(['code' => 'EUNKNOWN']);
    });

    it('defaults an omitted code to UNKNOWN', function () {
        $error = SessionFsError::fromArray([]);

        expect($error->code)->toBe(SessionFsErrorCode::UNKNOWN);
    });

    it('round trips the writeChanged flag', function () {
        $error = SessionFsError::fromArray([
            'code' => 'UNKNOWN',
            'message' => 'Write failed',
            'writeChanged' => true,
        ]);

        expect($error->writeChanged)->toBeTrue()
            ->and($error->toArray())->toBe([
                'code' => 'UNKNOWN',
                'message' => 'Write failed',
                'writeChanged' => true,
            ]);
    });

    it('omits a null writeChanged flag when converted to array', function () {
        $error = new SessionFsError(code: SessionFsErrorCode::ENOENT);

        expect($error->toArray())->not->toHaveKey('writeChanged');
    });
});
