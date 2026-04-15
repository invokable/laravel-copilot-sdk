<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingHistory;
use Revolution\Copilot\Types\Rpc\HistoryCompactResult;
use Revolution\Copilot\Types\Rpc\HistoryTruncateRequest;
use Revolution\Copilot\Types\Rpc\HistoryTruncateResult;

describe('PendingHistory', function () {
    it('calls session.history.compact and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.history.compact',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'),
            )
            ->andReturn([
                'success' => true,
                'tokensRemoved' => 1500,
                'messagesRemoved' => 10,
            ]);

        $pending = new PendingHistory($client, 'session-abc');
        $result = $pending->compact();

        expect($result)->toBeInstanceOf(HistoryCompactResult::class)
            ->and($result->success)->toBeTrue()
            ->and($result->tokensRemoved)->toBe(1500)
            ->and($result->messagesRemoved)->toBe(10);
    });

    it('calls session.history.truncate with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.history.truncate',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['eventId'] === 'evt-123'),
            )
            ->andReturn([
                'eventsRemoved' => 5,
            ]);

        $pending = new PendingHistory($client, 'session-abc');
        $result = $pending->truncate(new HistoryTruncateRequest(eventId: 'evt-123'));

        expect($result)->toBeInstanceOf(HistoryTruncateResult::class)
            ->and($result->eventsRemoved)->toBe(5);
    });

    it('calls session.history.truncate with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.history.truncate',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['eventId'] === 'evt-456'),
            )
            ->andReturn([
                'eventsRemoved' => 3,
            ]);

        $pending = new PendingHistory($client, 'session-abc');
        $result = $pending->truncate(['eventId' => 'evt-456']);

        expect($result)->toBeInstanceOf(HistoryTruncateResult::class)
            ->and($result->eventsRemoved)->toBe(3);
    });
});
