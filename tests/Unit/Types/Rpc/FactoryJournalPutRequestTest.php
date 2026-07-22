<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryJournalPutRequest;

describe('FactoryJournalPutRequest', function () {
    it('can be created from array', function () {
        $request = FactoryJournalPutRequest::fromArray([
            'key' => 'my-key',
            'resultJson' => ['a' => 1],
            'runId' => 'run-1',
        ]);

        expect($request->key)->toBe('my-key')
            ->and($request->resultJson)->toBe(['a' => 1])
            ->and($request->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryJournalPutRequest(key: 'my-key', resultJson: ['a' => 1], runId: 'run-1');

        expect($request->toArray())->toBe([
            'key' => 'my-key',
            'resultJson' => ['a' => 1],
            'runId' => 'run-1',
        ]);
    });
});
