<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFSErrorCode;
use Revolution\Copilot\Types\Rpc\SessionFSError;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileResult;

describe('SessionFsWriteFileResult', function () {
    it('creates a nested error from array data', function () {
        $result = SessionFsWriteFileResult::fromArray([
            'error' => [
                'code' => 'ENOENT',
                'message' => 'File not found',
            ],
        ]);

        expect($result->error)->toBeInstanceOf(SessionFSError::class)
            ->and($result->error->code)->toBe(SessionFSErrorCode::ENOENT)
            ->and($result->error->message)->toBe('File not found');
    });

    it('defaults to no error when error data is missing', function () {
        $result = SessionFsWriteFileResult::fromArray([]);

        expect($result->error)->toBeNull()
            ->and($result->toArray())->toBe([]);
    });

    it('converts a nested error to array data', function () {
        $result = new SessionFsWriteFileResult(
            error: new SessionFSError(
                code: SessionFSErrorCode::UNKNOWN,
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
