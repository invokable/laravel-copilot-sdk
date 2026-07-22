<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryAbortRequest;

describe('FactoryAbortRequest', function () {
    it('can be created from array', function () {
        $request = FactoryAbortRequest::fromArray([
            'sessionId' => 'session-1',
            'runId' => 'run-1',
        ]);

        expect($request->sessionId)->toBe('session-1')
            ->and($request->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryAbortRequest(sessionId: 'session-1', runId: 'run-1');

        expect($request->toArray())->toBe([
            'sessionId' => 'session-1',
            'runId' => 'run-1',
        ]);
    });
});
