<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingFactoryJournal;
use Revolution\Copilot\Types\Rpc\FactoryAckResult;
use Revolution\Copilot\Types\Rpc\FactoryJournalGetRequest;
use Revolution\Copilot\Types\Rpc\FactoryJournalGetResult;
use Revolution\Copilot\Types\Rpc\FactoryJournalPutRequest;

describe('PendingFactoryJournal', function () {
    it('calls session.factory.journal.get and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.factory.journal.get', [
                'key' => 'my-key',
                'runId' => 'run-1',
                'sessionId' => 'session-xyz',
            ])
            ->andReturn(['hit' => true, 'resultJson' => ['a' => 1]]);

        $pending = new PendingFactoryJournal($client, 'session-xyz');
        $result = $pending->get(new FactoryJournalGetRequest(key: 'my-key', runId: 'run-1'));

        expect($result)->toBeInstanceOf(FactoryJournalGetResult::class)
            ->and($result->hit)->toBeTrue()
            ->and($result->resultJson)->toBe(['a' => 1]);
    });

    it('calls session.factory.journal.put and returns ack', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.factory.journal.put', [
                'key' => 'my-key',
                'resultJson' => ['a' => 1],
                'runId' => 'run-1',
                'sessionId' => 'session-xyz',
            ])
            ->andReturn([]);

        $pending = new PendingFactoryJournal($client, 'session-xyz');
        $result = $pending->put(new FactoryJournalPutRequest(key: 'my-key', resultJson: ['a' => 1], runId: 'run-1'));

        expect($result)->toBeInstanceOf(FactoryAckResult::class);
    });
});
