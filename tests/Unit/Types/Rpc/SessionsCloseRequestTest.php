<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionsCloseRequest;

describe('SessionsCloseRequest', function () {
    it('can be created from array data', function () {
        $request = SessionsCloseRequest::fromArray([
            'sessionId' => 'session-123',
        ]);

        expect($request->sessionId)->toBe('session-123');
    });

    it('converts the session identifier to array data', function () {
        $request = new SessionsCloseRequest(sessionId: 'session-123');

        expect($request->toArray())->toBe([
            'sessionId' => 'session-123',
        ]);
    });
});
