<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryExecuteRequest;

describe('FactoryExecuteRequest', function () {
    it('can be created from array', function () {
        $request = FactoryExecuteRequest::fromArray([
            'args' => ['a' => 1],
            'name' => 'my-factory',
            'runId' => 'run-1',
            'sessionId' => 'session-1',
        ]);

        expect($request->args)->toBe(['a' => 1])
            ->and($request->name)->toBe('my-factory')
            ->and($request->runId)->toBe('run-1')
            ->and($request->sessionId)->toBe('session-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryExecuteRequest(
            args: ['a' => 1],
            name: 'my-factory',
            runId: 'run-1',
            sessionId: 'session-1',
        );

        expect($request->toArray())->toBe([
            'args' => ['a' => 1],
            'name' => 'my-factory',
            'runId' => 'run-1',
            'sessionId' => 'session-1',
        ]);
    });
});
