<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFSErrorCode;
use Revolution\Copilot\Types\Rpc\SessionFSError;

describe('SessionFSError', function () {
    it('can be created from array with a code and message', function () {
        $error = SessionFSError::fromArray([
            'code' => 'ENOENT',
            'message' => 'File not found',
        ]);

        expect($error->code)->toBe(SessionFSErrorCode::ENOENT)
            ->and($error->message)->toBe('File not found');
    });

    it('defaults an omitted message to null', function () {
        $error = SessionFSError::fromArray(['code' => 'UNKNOWN']);

        expect($error->message)->toBeNull();
    });

    it('converts the code and message to array values', function () {
        $error = new SessionFSError(
            code: SessionFSErrorCode::UNKNOWN,
            message: 'Unexpected filesystem error',
        );

        expect($error->toArray())->toBe([
            'code' => 'UNKNOWN',
            'message' => 'Unexpected filesystem error',
        ]);
    });

    it('omits a null message when converted to array', function () {
        $error = new SessionFSError(code: SessionFSErrorCode::ENOENT);

        expect($error->toArray())->toBe(['code' => 'ENOENT']);
    });

    it('rejects an unknown error code', function () {
        expect(fn () => SessionFSError::fromArray(['code' => 'EUNKNOWN']))
            ->toThrow(ValueError::class);
    });
});
