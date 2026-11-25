<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFsErrorCode;
use Revolution\Copilot\Types\Rpc\SessionFsError;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileResult;

describe('SessionFsWriteFileResult', function () {
    it('creates a nested error from array data', function () {
        $result = SessionFsWriteFileResult::fromArray([
            'error' => [
                'code' => 'ENOENT',
                'message' => 'File not found',
            ],
        ]);

        expect($result->error)->toBeInstanceOf(SessionFsError::class)
            ->and($result->error->code)->toBe(SessionFsErrorCode::ENOENT)
            ->and($result->error->message)->toBe('File not found');
    });

    it('defaults to no error when error data is missing', function () {
        $result = SessionFsWriteFileResult::fromArray([]);

        expect($result->error)->toBeNull()
            ->and($result->toArray())->toBe([]);
    });

    it('converts a nested error to array data', function () {
        $result = new SessionFsWriteFileResult(
            error: new SessionFsError(
                code: SessionFsErrorCode::UNKNOWN,
                message: 'Unexpected filesystem error',
            ),
        );

        expect($result->toArray())->toBe([
            'error' => [
                'code' => 'UNKNOWN',
                'message' => 'Unexpected filesystem error',
            ],
        ]);
    });

    it('omits a null error when converted to array', function () {
        $result = new SessionFsWriteFileResult;

        expect($result->toArray())->toBe([]);
    });
});
