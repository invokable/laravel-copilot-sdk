<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\EventsReadDirection;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessions;
use Revolution\Copilot\Types\Rpc\EventsReadResult;
use Revolution\Copilot\Types\Rpc\SessionBulkDeleteResult;
use Revolution\Copilot\Types\Rpc\SessionList;
use Revolution\Copilot\Types\Rpc\SessionPruneResult;
use Revolution\Copilot\Types\Rpc\SessionsBulkDeleteRequest;
use Revolution\Copilot\Types\Rpc\SessionsCheckInUseResult;
use Revolution\Copilot\Types\Rpc\SessionsFindByPrefixResult;
use Revolution\Copilot\Types\Rpc\SessionsFindByTaskIdResult;
use Revolution\Copilot\Types\Rpc\SessionsForkRequest;
use Revolution\Copilot\Types\Rpc\SessionsForkResult;
use Revolution\Copilot\Types\Rpc\SessionsGetLastForContextResult;
use Revolution\Copilot\Types\Rpc\SessionSizes;
use Revolution\Copilot\Types\Rpc\SessionsListRequest;
use Revolution\Copilot\Types\Rpc\SessionsPruneOldRequest;
use Revolution\Copilot\Types\Rpc\SessionsReadPersistedEventsRequest;
use Revolution\Copilot\Types\SessionListFilter;

describe('PendingSessions', function () {
    it('calls sessions.fork with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'sessions.fork',
                Mockery::on(fn ($params) => $params['sessionId'] === 'source-session'
                    && ! isset($params['toEventId'])),
            )
            ->andReturn([
                'sessionId' => 'forked-session-id',
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->fork(new SessionsForkRequest(sessionId: 'source-session'));

        expect($result)->toBeInstanceOf(SessionsForkResult::class)
            ->and($result->sessionId)->toBe('forked-session-id');
    });

    it('calls sessions.fork with toEventId', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'sessions.fork',
                Mockery::on(fn ($params) => $params['sessionId'] === 'source-session'
                    && $params['toEventId'] === 'evt-boundary'),
            )
            ->andReturn([
                'sessionId' => 'forked-session-id',
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->fork(new SessionsForkRequest(
            sessionId: 'source-session',
            toEventId: 'evt-boundary',
        ));

        expect($result)->toBeInstanceOf(SessionsForkResult::class)
            ->and($result->sessionId)->toBe('forked-session-id');
    });

    it('calls sessions.fork with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'sessions.fork',
                Mockery::on(fn ($params) => $params['sessionId'] === 'source-session'),
            )
            ->andReturn([
                'sessionId' => 'forked-session-id',
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->fork(['sessionId' => 'source-session']);

        expect($result)->toBeInstanceOf(SessionsForkResult::class)
            ->and($result->sessionId)->toBe('forked-session-id');
    });

    it('lists local and remote sessions with typed metadata', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.list', [
                'source' => 'local',
                'metadataLimit' => 0,
                'filter' => ['cwd' => '/workspace'],
                'includeDetached' => false,
            ])
            ->andReturn([
                'sessions' => [
                    [
                        'sessionId' => 'local-session',
                        'startTime' => '2026-10-01T00:00:00Z',
                        'modifiedTime' => '2026-10-02T00:00:00Z',
                        'isRemote' => false,
                    ],
                    [
                        'sessionId' => 'remote-session',
                        'startTime' => '2026-10-03T00:00:00Z',
                        'modifiedTime' => '2026-10-04T00:00:00Z',
                        'isRemote' => true,
                    ],
                ],
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->list(new SessionsListRequest(
            source: 'local',
            metadataLimit: 0,
            filter: new SessionListFilter(cwd: '/workspace'),
            includeDetached: false,
        ));

        expect($result)->toBeInstanceOf(SessionList::class)
            ->and($result->sessions[0]->isRemote)->toBeFalse()
            ->and($result->sessions[1]->isRemote)->toBeTrue();
    });

    it('reads persisted events with typed cursor options', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.readPersistedEvents', [
                'sessionId' => 'session-id',
                'cursor' => 'next-page',
                'max' => 20,
                'direction' => 'backward',
            ])
            ->andReturn([
                'events' => [],
                'cursor' => 'previous-page',
                'hasMore' => true,
                'cursorStatus' => 'ok',
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->readPersistedEvents(new SessionsReadPersistedEventsRequest(
            sessionId: 'session-id',
            cursor: 'next-page',
            max: 20,
            direction: EventsReadDirection::BACKWARD,
        ));

        expect($result)->toBeInstanceOf(EventsReadResult::class)
            ->and($result->cursor)->toBe('previous-page')
            ->and($result->hasMore)->toBeTrue();
    });

    it('returns null session IDs when lookup RPCs find no match', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.findByTaskId', ['taskId' => 'missing-task'])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.findByPrefix', ['prefix' => 'missing-prefix'])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.getLastForContext', [])
            ->andReturn([]);

        $pending = new PendingSessions($client);
        $byTask = $pending->findByTaskId(['taskId' => 'missing-task']);
        $byPrefix = $pending->findByPrefix(['prefix' => 'missing-prefix']);
        $lastForContext = $pending->getLastForContext();

        expect($byTask)->toBeInstanceOf(SessionsFindByTaskIdResult::class)
            ->and($byTask->sessionId)->toBeNull()
            ->and($byPrefix)->toBeInstanceOf(SessionsFindByPrefixResult::class)
            ->and($byPrefix->sessionId)->toBeNull()
            ->and($lastForContext)->toBeInstanceOf(SessionsGetLastForContextResult::class)
            ->and($lastForContext->sessionId)->toBeNull();
    });

    it('gets persisted session sizes without parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.getSizes', [])
            ->andReturn(['sizes' => ['session-id' => 1024]]);

        $pending = new PendingSessions($client);
        $result = $pending->getSizes();

        expect($result)->toBeInstanceOf(SessionSizes::class)
            ->and($result->sizes)->toBe(['session-id' => 1024]);
    });

    it('checks which requested sessions are in use', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.checkInUse', ['sessionIds' => ['session-id', 'other-id']])
            ->andReturn(['inUse' => ['session-id']]);

        $pending = new PendingSessions($client);
        $result = $pending->checkInUse(['sessionIds' => ['session-id', 'other-id']]);

        expect($result)->toBeInstanceOf(SessionsCheckInUseResult::class)
            ->and($result->inUse)->toBe(['session-id']);
    });

    it('bulk deletes persisted sessions and returns freed bytes', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.bulkDelete', ['sessionIds' => ['session-id']])
            ->andReturn(['freedBytes' => ['session-id' => 512]]);

        $pending = new PendingSessions($client);
        $result = $pending->bulkDelete(new SessionsBulkDeleteRequest(sessionIds: ['session-id']));

        expect($result)->toBeInstanceOf(SessionBulkDeleteResult::class)
            ->and($result->freedBytes)->toBe(['session-id' => 512]);
    });

    it('prunes old sessions with optional parameters omitted', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.pruneOld', ['olderThanDays' => 30])
            ->andReturn([
                'deleted' => [],
                'candidates' => ['session-id'],
                'skipped' => [],
                'freedBytes' => 0,
                'dryRun' => false,
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->pruneOld(new SessionsPruneOldRequest(olderThanDays: 30));

        expect($result)->toBeInstanceOf(SessionPruneResult::class)
            ->and($result->candidates)->toBe(['session-id'])
            ->and($result->dryRun)->toBeFalse();
    });
});
