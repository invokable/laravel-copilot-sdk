<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionsSaveRequest;

describe('SessionsSaveRequest', function () {
    it('creates a request from session identifier data', function () {
        $request = SessionsSaveRequest::fromArray([
            'sessionId' => 'session-123',
        ]);

        expect($request->sessionId)->toBe('session-123');
    });

    it('serializes the session identifier', function () {
        $request = new SessionsSaveRequest(sessionId: 'session-123');

        expect($request->toArray())->toBe([
            'sessionId' => 'session-123',
        ]);
    });
});
