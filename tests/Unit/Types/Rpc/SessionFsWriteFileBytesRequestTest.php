<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionFsWriteFileBytesRequest;

describe('SessionFsWriteFileBytesRequest', function () {
    it('can be created from array with all fields', function () {
        $request = SessionFsWriteFileBytesRequest::fromArray([
            'content' => 'AAEC/w==',
            'path' => '/tmp/data.bin',
            'sessionId' => 'session-123',
            'mode' => '420',
        ]);

        expect($request->content)->toBe('AAEC/w==')
            ->and($request->path)->toBe('/tmp/data.bin')
            ->and($request->sessionId)->toBe('session-123')
            ->and($request->mode)->toBe(420);
    });

    it('defaults an omitted mode to null', function () {
        $request = SessionFsWriteFileBytesRequest::fromArray([
            'content' => 'AAEC/w==',
            'path' => '/tmp/data.bin',
            'sessionId' => 'session-123',
        ]);

        expect($request->mode)->toBeNull();
    });

    it('omits a null mode when converted to array', function () {
        $request = new SessionFsWriteFileBytesRequest(
            content: 'AAEC/w==',
            path: '/tmp/data.bin',
            sessionId: 'session-123',
        );

        expect($request->toArray())->toBe([
            'content' => 'AAEC/w==',
            'path' => '/tmp/data.bin',
            'sessionId' => 'session-123',
        ]);
    });

    it('preserves a zero mode when converted to array', function () {
        $request = new SessionFsWriteFileBytesRequest(
            content: '',
            path: '/tmp/empty.bin',
            sessionId: 'session-456',
            mode: 0,
        );

        expect($request->toArray())->toBe([
            'content' => '',
            'path' => '/tmp/empty.bin',
            'sessionId' => 'session-456',
            'mode' => 0,
        ]);
    });
});
