<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSchedule;
use Revolution\Copilot\Types\Rpc\ScheduleAddAtRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddCronRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddResult;
use Revolution\Copilot\Types\Rpc\ScheduleList;
use Revolution\Copilot\Types\Rpc\ScheduleStopRequest;
use Revolution\Copilot\Types\Rpc\ScheduleStopResult;

describe('PendingSchedule', function () {
    it('calls session.schedule.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.schedule.list', ['sessionId' => 'session-xyz'])
            ->andReturn([
                'entries' => [[
                    'id' => 1,
                    'intervalMs' => 1800000,
                    'nextRunAt' => '2026-01-01T00:00:00Z',
                    'prompt' => 'check',
                    'recurring' => true,
                ]],
            ]);

        $pending = new PendingSchedule($client, 'session-xyz');

        $result = $pending->list();

        expect($result)->toBeInstanceOf(ScheduleList::class)
            ->and($result->entries)->toHaveCount(1)
            ->and($result->entries[0]->prompt)->toBe('check');
    });

    it('calls session.schedule.stop and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.schedule.stop', ['id' => 1, 'sessionId' => 'session-xyz'])
            ->andReturn([]);

        $pending = new PendingSchedule($client, 'session-xyz');

        expect($pending->stop(new ScheduleStopRequest(id: 1)))->toBeInstanceOf(ScheduleStopResult::class);
    });

    it('calls session.schedule.hydrate', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.schedule.hydrate', ['sessionId' => 'session-xyz'])
            ->andReturn([]);

        $pending = new PendingSchedule($client, 'session-xyz');

        expect($pending->hydrate())->toBeNull();
    });

    it('calls session.schedule.add and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.schedule.add',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['interval'] === '5m'
                    && $params['prompt'] === 'check'),
            )
            ->andReturn(['entry' => ['id' => 1, 'prompt' => 'check', 'recurring' => true]]);

        $pending = new PendingSchedule($client, 'session-xyz');
        $result = $pending->add(new ScheduleAddRequest(interval: '5m', prompt: 'check'));

        expect($result)->toBeInstanceOf(ScheduleAddResult::class)
            ->and($result->entry->id)->toBe(1);
    });

    it('calls session.schedule.addCron and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.schedule.addCron',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['cron'] === '* * * * *'),
            )
            ->andReturn(['entry' => ['id' => 2, 'prompt' => 'nightly', 'recurring' => true]]);

        $pending = new PendingSchedule($client, 'session-xyz');
        $result = $pending->addCron(new ScheduleAddCronRequest(cron: '* * * * *', prompt: 'nightly'));

        expect($result)->toBeInstanceOf(ScheduleAddResult::class)
            ->and($result->entry->id)->toBe(2);
    });

    it('calls session.schedule.addAt and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.schedule.addAt',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['at'] === 1234567890),
            )
            ->andReturn(['error' => 'in the past']);

        $pending = new PendingSchedule($client, 'session-xyz');
        $result = $pending->addAt(new ScheduleAddAtRequest(at: 1234567890, prompt: 'once'));

        expect($result)->toBeInstanceOf(ScheduleAddResult::class)
            ->and($result->error)->toBe('in the past');
    });

    it('calls session.schedule.addSelfPaced with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.schedule.addSelfPaced',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['prompt'] === 'self'),
            )
            ->andReturn(['entry' => ['id' => 3, 'prompt' => 'self']]);

        $pending = new PendingSchedule($client, 'session-xyz');
        $result = $pending->addSelfPaced(['prompt' => 'self']);

        expect($result)->toBeInstanceOf(ScheduleAddResult::class)
            ->and($result->entry->id)->toBe(3);
    });

    it('calls session.schedule.hasSelfPaced', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.schedule.hasSelfPaced', ['sessionId' => 'session-xyz'])
            ->andReturn(['hasSelfPaced' => true]);

        $result = (new PendingSchedule($client, 'session-xyz'))->hasSelfPaced();

        expect($result->hasSelfPaced)->toBeTrue();
    });

    it('calls session.schedule.rearmSelfPaced', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.schedule.rearmSelfPaced', [
                'id' => 3,
                'at' => 1_725_000_000_000,
                'sessionId' => 'session-xyz',
            ])
            ->andReturn(['error' => null]);

        $result = (new PendingSchedule($client, 'session-xyz'))->rearmSelfPaced([
            'id' => 3,
            'at' => 1_725_000_000_000,
        ]);

        expect($result)->toBeInstanceOf(ScheduleAddResult::class)
            ->and($result->entry)->toBeNull()
            ->and($result->error)->toBeNull();
    });
});
