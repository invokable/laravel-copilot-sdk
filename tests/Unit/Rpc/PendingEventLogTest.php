<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingEventLog;
use Revolution\Copilot\Types\Rpc\EventLogReadRequest;
use Revolution\Copilot\Types\Rpc\EventLogReleaseInterestResult;
use Revolution\Copilot\Types\Rpc\EventLogTailResult;
use Revolution\Copilot\Types\Rpc\EventsReadResult;
use Revolution\Copilot\Types\Rpc\RegisterEventInterestParams;
use Revolution\Copilot\Types\Rpc\RegisterEventInterestResult;
use Revolution\Copilot\Types\Rpc\ReleaseEventInterestParams;

describe('PendingEventLog', function () {
    it('calls session.eventLog.read with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.eventLog.read',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['cursor'] === 'cursor-1'
                    && $params['max'] === 10),
            )
            ->andReturn([
                'events' => [
                    [
                        'id' => 'evt-1',
                        'timestamp' => '2026-01-24T10:00:00Z',
                        'type' => 'assistant_message',
                        'data' => ['content' => 'hello'],
                    ],
                ],
                'cursor' => 'cursor-2',
                'hasMore' => false,
                'cursorStatus' => 'ok',
            ]);

        $pending = new PendingEventLog($client, 'test-session');
        $result = $pending->read(new EventLogReadRequest(cursor: 'cursor-1', max: 10));

        expect($result)->toBeInstanceOf(EventsReadResult::class)
            ->and($result->cursor)->toBe('cursor-2')
            ->and($result->events)->toHaveCount(1)
            ->and($result->events[0]->id)->toBe('evt-1');
    });

    it('calls session.eventLog.tail', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.eventLog.tail', ['sessionId' => 'test-session'])
            ->andReturn(['cursor' => 'tail-cursor']);

        $pending = new PendingEventLog($client, 'test-session');
        $result = $pending->tail();

        expect($result)->toBeInstanceOf(EventLogTailResult::class)
            ->and($result->cursor)->toBe('tail-cursor');
    });

    it('calls session.eventLog.registerInterest', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.eventLog.registerInterest',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['eventType'] === 'mcp.oauth_required'),
            )
            ->andReturn(['handle' => 'interest-1']);

        $pending = new PendingEventLog($client, 'test-session');
        $result = $pending->registerInterest(new RegisterEventInterestParams(eventType: 'mcp.oauth_required'));

        expect($result)->toBeInstanceOf(RegisterEventInterestResult::class)
            ->and($result->handle)->toBe('interest-1');
    });

    it('calls session.eventLog.releaseInterest', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.eventLog.releaseInterest',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['handle'] === 'interest-1'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingEventLog($client, 'test-session');
        $result = $pending->releaseInterest(new ReleaseEventInterestParams(handle: 'interest-1'));

        expect($result)->toBeInstanceOf(EventLogReleaseInterestResult::class)
            ->and($result->success)->toBeTrue();
    });
});
