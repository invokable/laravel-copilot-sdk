<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFsErrorCode;
use Revolution\Copilot\Types\Rpc\SessionFsError;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileBytesResult;

describe('SessionFsWriteFileBytesResult', function () {
    it('creates an error from array data', function () {
        $result = SessionFsWriteFileBytesResult::fromArray([
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
        $result = SessionFsWriteFileBytesResult::fromArray([]);

        expect($result->error)->toBeNull()
            ->and($result->toArray())->toBe([]);
    });

    it('converts a nested error to array data', function () {
        $result = new SessionFsWriteFileBytesResult(
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
        $result = new SessionFsWriteFileBytesResult;

        expect($result->toArray())->toBe([]);
    });
});
