<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryGetRunRequest;

describe('FactoryGetRunRequest', function () {
    it('can be created from array', function () {
        $request = FactoryGetRunRequest::fromArray(['runId' => 'run-1']);

        expect($request->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryGetRunRequest(runId: 'run-1');

        expect($request->toArray())->toBe(['runId' => 'run-1']);
    });
});
