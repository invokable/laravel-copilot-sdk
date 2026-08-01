<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingHistory;
use Revolution\Copilot\Types\Rpc\HistoryCompactRequest;
use Revolution\Copilot\Types\Rpc\HistoryCompactResult;
use Revolution\Copilot\Types\Rpc\HistoryListRewindPointsResult;
use Revolution\Copilot\Types\Rpc\HistoryPreviewRewindRequest;
use Revolution\Copilot\Types\Rpc\HistoryPreviewRewindResult;
use Revolution\Copilot\Types\Rpc\HistoryRewindRequest;
use Revolution\Copilot\Types\Rpc\HistoryRewindResult;
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

    it('calls session.history.compact with optional params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.history.compact', [
                'customInstructions' => 'Focus on decisions',
                'trigger' => 'manual',
                'tokenLimit' => 10000,
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'success' => true,
                'tokensRemoved' => 0,
                'messagesRemoved' => 0,
            ]);

        $result = (new PendingHistory($client, 'session-abc'))->compact(new HistoryCompactRequest(
            customInstructions: 'Focus on decisions',
            trigger: 'manual',
            tokenLimit: 10000,
        ));

        expect($result)->toBeInstanceOf(HistoryCompactResult::class);
    });

    it('lists history rewind points', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.history.listRewindPoints', ['sessionId' => 'session-abc'])
            ->andReturn([
                'fileChangeTrackingEnabled' => true,
                'points' => [],
            ]);

        $result = (new PendingHistory($client, 'session-abc'))->listRewindPoints();

        expect($result)->toBeInstanceOf(HistoryListRewindPointsResult::class)
            ->and($result->fileChangeTrackingEnabled)->toBeTrue();
    });

    it('previews a history rewind', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.history.previewRewind', [
                'eventId' => 'evt-rewind',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'available' => true,
                'fileCount' => 0,
                'files' => [],
            ]);

        $result = (new PendingHistory($client, 'session-abc'))->previewRewind(
            new HistoryPreviewRewindRequest(eventId: 'evt-rewind'),
        );

        expect($result)->toBeInstanceOf(HistoryPreviewRewindResult::class)
            ->and($result->available)->toBeTrue();
    });

    it('rewinds history', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.history.rewind', [
                'eventId' => 'evt-rewind',
                'mode' => 'conversation',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'outcome' => 'success',
                'restoredFiles' => [],
                'skippedFiles' => [],
            ]);

        $result = (new PendingHistory($client, 'session-abc'))->rewind(new HistoryRewindRequest(
            eventId: 'evt-rewind',
            mode: 'conversation',
        ));

        expect($result)->toBeInstanceOf(HistoryRewindResult::class)
            ->and($result->restoredFiles)->toBe([]);
    });
});
