<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionFsReadFileBytesRequest;

describe('SessionFsReadFileBytesRequest', function () {
    it('can be created from array with all fields', function () {
        $request = SessionFsReadFileBytesRequest::fromArray([
            'path' => '/tmp/data.bin',
            'sessionId' => 'session-123',
        ]);

        expect($request->path)->toBe('/tmp/data.bin')
            ->and($request->sessionId)->toBe('session-123');
    });

    it('converts to array', function () {
        $request = new SessionFsReadFileBytesRequest(
            path: '/tmp/data.bin',
            sessionId: 'session-123',
        );

        expect($request->toArray())->toBe([
            'path' => '/tmp/data.bin',
            'sessionId' => 'session-123',
        ]);
    });

    it('requires a path when created from array', function () {
        expect(fn () => SessionFsReadFileBytesRequest::fromArray([
            'sessionId' => 'session-123',
        ]))->toThrow(InvalidArgumentException::class);
    });

    it('requires a session id when created from array', function () {
        expect(fn () => SessionFsReadFileBytesRequest::fromArray([
            'path' => '/tmp/data.bin',
        ]))->toThrow(InvalidArgumentException::class);
    });
});
