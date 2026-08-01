<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryJournalGetRequest;

describe('FactoryJournalGetRequest', function () {
    it('can be created from array', function () {
        $request = FactoryJournalGetRequest::fromArray([
            'executionToken' => 'token-1',
            'key' => 'my-key',
            'runId' => 'run-1',
        ]);

        expect($request->executionToken)->toBe('token-1')
            ->and($request->key)->toBe('my-key')
            ->and($request->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryJournalGetRequest(executionToken: 'token-1', key: 'my-key', runId: 'run-1');

        expect($request->toArray())->toBe([
            'executionToken' => 'token-1',
            'key' => 'my-key',
            'runId' => 'run-1',
        ]);
    });
});
