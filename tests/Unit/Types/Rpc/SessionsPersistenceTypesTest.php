<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\EventsReadDirection;
use Revolution\Copilot\Types\Rpc\SessionBulkDeleteResult;
use Revolution\Copilot\Types\Rpc\SessionList;
use Revolution\Copilot\Types\Rpc\SessionPruneResult;
use Revolution\Copilot\Types\Rpc\SessionsBulkDeleteRequest;
use Revolution\Copilot\Types\Rpc\SessionsCheckInUseRequest;
use Revolution\Copilot\Types\Rpc\SessionsCheckInUseResult;
use Revolution\Copilot\Types\Rpc\SessionsFindByPrefixRequest;
use Revolution\Copilot\Types\Rpc\SessionsFindByPrefixResult;
use Revolution\Copilot\Types\Rpc\SessionsFindByTaskIdRequest;
use Revolution\Copilot\Types\Rpc\SessionsFindByTaskIdResult;
use Revolution\Copilot\Types\Rpc\SessionsGetLastForContextRequest;
use Revolution\Copilot\Types\Rpc\SessionsGetLastForContextResult;
use Revolution\Copilot\Types\Rpc\SessionSizes;
use Revolution\Copilot\Types\Rpc\SessionsListRequest;
use Revolution\Copilot\Types\Rpc\SessionsPruneOldRequest;
use Revolution\Copilot\Types\Rpc\SessionsReadPersistedEventsRequest;
use Revolution\Copilot\Types\SessionContext;
use Revolution\Copilot\Types\SessionListFilter;
use Revolution\Copilot\Types\SessionMetadata;

it('round trips persisted session RPC types through arrays', function () {
    $types = [
        new SessionsListRequest(
            source: 'local',
            metadataLimit: 0,
            filter: new SessionListFilter(cwd: '/workspace'),
            includeDetached: false,
            throwOnError: true,
        ),
        new SessionList(sessions: [
            new SessionMetadata(
                sessionId: 'local-id',
                startTime: '2026-10-01T00:00:00Z',
                modifiedTime: '2026-10-02T00:00:00Z',
                isRemote: false,
                context: new SessionContext(cwd: '/workspace'),
            ),
            new SessionMetadata(
                sessionId: 'remote-id',
                startTime: '2026-10-03T00:00:00Z',
                modifiedTime: '2026-10-04T00:00:00Z',
                isRemote: true,
            ),
        ]),
        new SessionsReadPersistedEventsRequest(
            sessionId: 'session-id',
            cursor: 'cursor',
            max: 0,
            direction: EventsReadDirection::BACKWARD,
        ),
        new SessionsFindByTaskIdRequest(taskId: 'task-id'),
        new SessionsFindByTaskIdResult(sessionId: 'session-id'),
        new SessionsFindByTaskIdResult,
        new SessionsFindByPrefixRequest(prefix: 'prefix'),
        new SessionsFindByPrefixResult(sessionId: 'session-id'),
        new SessionsFindByPrefixResult,
        new SessionsGetLastForContextRequest(context: new SessionContext(cwd: '/workspace')),
        new SessionsGetLastForContextRequest,
        new SessionsGetLastForContextResult(sessionId: 'session-id'),
        new SessionsGetLastForContextResult,
        new SessionSizes(sizes: ['session-id' => 1024]),
        new SessionsCheckInUseRequest(sessionIds: ['session-id']),
        new SessionsCheckInUseResult(inUse: ['session-id']),
        new SessionsBulkDeleteRequest(sessionIds: ['session-id']),
        new SessionBulkDeleteResult(freedBytes: ['session-id' => 512]),
        new SessionsPruneOldRequest(
            olderThanDays: 0,
            dryRun: false,
            includeNamed: true,
            excludeSessionIds: [],
        ),
        new SessionPruneResult(
            deleted: ['deleted-id'],
            candidates: ['candidate-id'],
            skipped: ['skipped-id'],
            freedBytes: 0,
            dryRun: true,
        ),
    ];

    foreach ($types as $type) {
        $typeClass = $type::class;
        $serialized = $type->toArray();

        expect($typeClass::fromArray($serialized)->toArray())->toBe($serialized);
    }
});
