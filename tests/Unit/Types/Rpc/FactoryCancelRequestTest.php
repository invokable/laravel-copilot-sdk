<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryCancelRequest;

describe('FactoryCancelRequest', function () {
    it('can be created from array', function () {
        $request = FactoryCancelRequest::fromArray(['runId' => 'run-1']);

        expect($request->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryCancelRequest(runId: 'run-1');

        expect($request->toArray())->toBe(['runId' => 'run-1']);
    });
});
