<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionsForkParams;
use Revolution\Copilot\Types\Rpc\SessionsForkResult;

describe('SessionsForkResult', function () {
    it('can be created from array', function () {
        $result = SessionsForkResult::fromArray([
            'sessionId' => 'forked-session-123',
        ]);

        expect($result->sessionId)->toBe('forked-session-123');
    });

    it('can convert to array', function () {
        $result = new SessionsForkResult(sessionId: 'forked-session-456');

        expect($result->toArray())->toBe([
            'sessionId' => 'forked-session-456',
        ]);
    });
});

describe('SessionsForkParams', function () {
    it('can be created from array with all fields', function () {
        $params = SessionsForkParams::fromArray([
            'sessionId' => 'source-session',
            'toEventId' => 'evt-boundary',
        ]);

        expect($params->sessionId)->toBe('source-session')
            ->and($params->toEventId)->toBe('evt-boundary');
    });

    it('can be created from array without optional fields', function () {
        $params = SessionsForkParams::fromArray([
            'sessionId' => 'source-session',
        ]);

        expect($params->sessionId)->toBe('source-session')
            ->and($params->toEventId)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $params = new SessionsForkParams(
            sessionId: 'source-session',
            toEventId: 'evt-boundary',
        );

        expect($params->toArray())->toBe([
            'sessionId' => 'source-session',
            'toEventId' => 'evt-boundary',
        ]);
    });

    it('excludes null optional fields from array', function () {
        $params = new SessionsForkParams(sessionId: 'source-session');

        expect($params->toArray())->toBe([
            'sessionId' => 'source-session',
        ]);
    });
});
