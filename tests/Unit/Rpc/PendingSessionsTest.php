<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\EventsReadDirection;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessions;
use Revolution\Copilot\Types\Rpc\EventsReadResult;
use Revolution\Copilot\Types\Rpc\InstalledPlugin;
use Revolution\Copilot\Types\Rpc\LocalSessionContext;
use Revolution\Copilot\Types\Rpc\LocalSessionMetadataValue;
use Revolution\Copilot\Types\Rpc\RemoteControlConfig;
use Revolution\Copilot\Types\Rpc\RemoteControlConfigExistingMcSession;
use Revolution\Copilot\Types\Rpc\RemoteControlStatusResult;
use Revolution\Copilot\Types\Rpc\RemoteControlStopResult;
use Revolution\Copilot\Types\Rpc\RemoteControlTransferResult;
use Revolution\Copilot\Types\Rpc\SessionBulkDeleteResult;
use Revolution\Copilot\Types\Rpc\SessionList;
use Revolution\Copilot\Types\Rpc\SessionLoadDeferredRepoHooksResult;
use Revolution\Copilot\Types\Rpc\SessionPruneResult;
use Revolution\Copilot\Types\Rpc\SessionsBulkDeleteRequest;
use Revolution\Copilot\Types\Rpc\SessionsCheckInUseResult;
use Revolution\Copilot\Types\Rpc\SessionsCloseRequest;
use Revolution\Copilot\Types\Rpc\SessionsCloseResult;
use Revolution\Copilot\Types\Rpc\SessionsEnrichMetadataRequest;
use Revolution\Copilot\Types\Rpc\SessionsEnrichMetadataResult;
use Revolution\Copilot\Types\Rpc\SessionsFindByPrefixResult;
use Revolution\Copilot\Types\Rpc\SessionsFindByTaskIdResult;
use Revolution\Copilot\Types\Rpc\SessionsForkRequest;
use Revolution\Copilot\Types\Rpc\SessionsForkResult;
use Revolution\Copilot\Types\Rpc\SessionsGetLastForContextResult;
use Revolution\Copilot\Types\Rpc\SessionSizes;
use Revolution\Copilot\Types\Rpc\SessionsListRequest;
use Revolution\Copilot\Types\Rpc\SessionsLoadDeferredRepoHooksRequest;
use Revolution\Copilot\Types\Rpc\SessionsPruneOldRequest;
use Revolution\Copilot\Types\Rpc\SessionsReadPersistedEventsRequest;
use Revolution\Copilot\Types\Rpc\SessionsReleaseLockResult;
use Revolution\Copilot\Types\Rpc\SessionsReloadPluginHooksRequest;
use Revolution\Copilot\Types\Rpc\SessionsReloadPluginHooksResult;
use Revolution\Copilot\Types\Rpc\SessionsSaveRequest;
use Revolution\Copilot\Types\Rpc\SessionsSaveResult;
use Revolution\Copilot\Types\Rpc\SessionsSetAdditionalPluginsRequest;
use Revolution\Copilot\Types\Rpc\SessionsSetAdditionalPluginsResult;
use Revolution\Copilot\Types\Rpc\SessionsSetRemoteControlSteeringRequest;
use Revolution\Copilot\Types\Rpc\SessionsStartRemoteControlRequest;
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

    it('starts remote control with nested configuration and returns active status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.startRemoteControl', [
                'sessionId' => 'session-remote-control',
                'config' => [
                    'remote' => true,
                    'steerable' => true,
                    'explicit' => true,
                    'silent' => false,
                    'existingMcSession' => [
                        'mcSessionId' => 'mc-session-1',
                        'mcTaskId' => 'mc-task-1',
                    ],
                ],
            ])
            ->andReturn([
                'status' => [
                    'state' => 'active',
                    'attachedSessionId' => 'session-remote-control',
                    'frontendUrl' => 'https://github.com/mission-control/session/1',
                    'isSteerable' => true,
                ],
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->startRemoteControl(new SessionsStartRemoteControlRequest(
            sessionId: 'session-remote-control',
            config: new RemoteControlConfig(
                remote: true,
                steerable: true,
                explicit: true,
                silent: false,
                existingMcSession: new RemoteControlConfigExistingMcSession(
                    mcSessionId: 'mc-session-1',
                    mcTaskId: 'mc-task-1',
                ),
            ),
        ));

        expect($result)->toBeInstanceOf(RemoteControlStatusResult::class)
            ->and($result->status->state)->toBe('active')
            ->and($result->status->frontendUrl)->toBe('https://github.com/mission-control/session/1');
    });

    it('stops remote control with an empty object and returns stopped status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.stopRemoteControl', [])
            ->andReturn([
                'status' => ['state' => 'off'],
                'stopped' => false,
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->stopRemoteControl();

        expect($result)->toBeInstanceOf(RemoteControlStopResult::class)
            ->and($result->status->state)->toBe('off')
            ->and($result->stopped)->toBeFalse();
    });

    it('transfers remote control with optional source omitted', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.transferRemoteControl', ['toSessionId' => 'session-target'])
            ->andReturn([
                'status' => [
                    'state' => 'connecting',
                    'attachedSessionId' => 'session-target',
                ],
                'transferred' => true,
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->transferRemoteControl(['toSessionId' => 'session-target']);

        expect($result)->toBeInstanceOf(RemoteControlTransferResult::class)
            ->and($result->status->state)->toBe('connecting')
            ->and($result->transferred)->toBeTrue();
    });

    it('sets remote-control steering with typed parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.setRemoteControlSteering', ['enabled' => false])
            ->andReturn([
                'status' => [
                    'state' => 'active',
                    'attachedSessionId' => 'session-remote-control',
                    'isSteerable' => false,
                ],
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->setRemoteControlSteering(
            new SessionsSetRemoteControlSteeringRequest(enabled: false),
        );

        expect($result)->toBeInstanceOf(RemoteControlStatusResult::class)
            ->and($result->status->state)->toBe('active')
            ->and($result->status->isSteerable)->toBeFalse();
    });

    it('gets remote-control status with an empty object', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.getRemoteControlStatus', [])
            ->andReturn([
                'status' => [
                    'state' => 'error',
                    'error' => 'Remote export failed',
                ],
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->getRemoteControlStatus();

        expect($result)->toBeInstanceOf(RemoteControlStatusResult::class)
            ->and($result->status->state)->toBe('error')
            ->and($result->status->error)->toBe('Remote export failed');
    });

    it('closes, saves, and releases locks for persisted sessions with empty results', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.close', ['sessionId' => 'session-to-close'])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.save', ['sessionId' => 'session-to-save'])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.releaseLock', ['sessionId' => 'session-to-unlock'])
            ->andReturn([]);

        $pending = new PendingSessions($client);
        $closed = $pending->close(new SessionsCloseRequest('session-to-close'));
        $saved = $pending->save(new SessionsSaveRequest('session-to-save'));
        $released = $pending->releaseLock(['sessionId' => 'session-to-unlock']);

        expect($closed)->toBeInstanceOf(SessionsCloseResult::class)
            ->and($closed->toArray())->toBe([])
            ->and($saved)->toBeInstanceOf(SessionsSaveResult::class)
            ->and($saved->toArray())->toBe([])
            ->and($released)->toBeInstanceOf(SessionsReleaseLockResult::class)
            ->and($released->toArray())->toBe([]);
    });

    it('omits an unspecified repository-hook deferral flag and preserves false', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.reloadPluginHooks', [
                'sessionId' => 'session-no-deferral',
            ])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.reloadPluginHooks', [
                'sessionId' => 'session-explicit-false',
                'deferRepoHooks' => false,
            ])
            ->andReturn([]);

        $pending = new PendingSessions($client);
        $omitted = $pending->reloadPluginHooks(['sessionId' => 'session-no-deferral']);
        $explicitFalse = $pending->reloadPluginHooks(new SessionsReloadPluginHooksRequest(
            sessionId: 'session-explicit-false',
            deferRepoHooks: false,
        ));

        expect($omitted)->toBeInstanceOf(SessionsReloadPluginHooksResult::class)
            ->and($omitted->toArray())->toBe([])
            ->and($explicitFalse)->toBeInstanceOf(SessionsReloadPluginHooksResult::class)
            ->and($explicitFalse->toArray())->toBe([]);
    });

    it('loads deferred repository hooks into a typed result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.loadDeferredRepoHooks', ['sessionId' => 'trusted-session'])
            ->andReturn([
                'startupPrompts' => ['Review repository instructions'],
                'hookCount' => 3,
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->loadDeferredRepoHooks(
            new SessionsLoadDeferredRepoHooksRequest('trusted-session'),
        );

        expect($result)->toBeInstanceOf(SessionLoadDeferredRepoHooksResult::class)
            ->and($result->startupPrompts)->toBe(['Review repository instructions'])
            ->and($result->hookCount)->toBe(3);

        expect(fn () => SessionLoadDeferredRepoHooksResult::fromArray([
            'startupPrompts' => [],
        ]))->toThrow(InvalidArgumentException::class);
    });

    it('sends an empty plugin list to clear additional plugins', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.setAdditionalPlugins', ['plugins' => []])
            ->andReturn([]);

        $pending = new PendingSessions($client);
        $result = $pending->setAdditionalPlugins(['plugins' => []]);

        expect($result)->toBeInstanceOf(SessionsSetAdditionalPluginsResult::class)
            ->and($result->toArray())->toBe([]);
    });

    it('replaces additional plugins using the upstream snake-case keys', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.setAdditionalPlugins', [
                'plugins' => [[
                    'name' => 'review-tools',
                    'marketplace' => 'team',
                    'version' => '1.2.0',
                    'installed_at' => '2026-10-17T09:00:00Z',
                    'enabled' => false,
                    'cache_path' => '/cache/review-tools',
                    'source' => ['repository' => 'org/review-tools'],
                    'source_sha' => 'abc123',
                    'installed_from' => '/marketplaces/team',
                ]],
            ])
            ->andReturn([]);

        $pending = new PendingSessions($client);
        $result = $pending->setAdditionalPlugins(new SessionsSetAdditionalPluginsRequest([
            new InstalledPlugin(
                name: 'review-tools',
                marketplace: 'team',
                version: '1.2.0',
                installedAt: '2026-10-17T09:00:00Z',
                enabled: false,
                cachePath: '/cache/review-tools',
                source: ['repository' => 'org/review-tools'],
                sourceSha: 'abc123',
                installedFrom: '/marketplaces/team',
            ),
        ]));

        expect($result)->toBeInstanceOf(SessionsSetAdditionalPluginsResult::class)
            ->and($result->toArray())->toBe([])
            ->and(InstalledPlugin::fromArray([
                'name' => 'minimal',
                'marketplace' => 'direct',
                'installed_at' => '2026-10-17T09:00:00Z',
                'enabled' => false,
                'cache_path' => '/cache/minimal',
            ])->toArray())->toBe([
                'name' => 'minimal',
                'marketplace' => 'direct',
                'installed_at' => '2026-10-17T09:00:00Z',
                'enabled' => false,
                'cache_path' => '/cache/minimal',
            ]);

        expect(InstalledPlugin::fromArray([
            'name' => 'uncached',
            'marketplace' => '',
            'installed_at' => '2026-10-17T09:00:00Z',
            'enabled' => true,
        ])->toArray())->toBe([
            'name' => 'uncached',
            'marketplace' => '',
            'installed_at' => '2026-10-17T09:00:00Z',
            'enabled' => true,
        ]);
    });

    it('enriches session metadata and decodes typed local metadata records', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.enrichMetadata', [
                'sessions' => [[
                    'sessionId' => 'local-session',
                    'startTime' => '2026-10-16T09:00:00Z',
                    'modifiedTime' => '2026-10-17T09:00:00Z',
                    'isRemote' => false,
                    'isDetached' => false,
                    'context' => [
                        'cwd' => '/workspace',
                        'hostType' => 'github',
                    ],
                ]],
            ])
            ->andReturn([
                'sessions' => [[
                    'sessionId' => 'local-session',
                    'startTime' => '2026-10-16T09:00:00Z',
                    'modifiedTime' => '2026-10-17T09:00:00Z',
                    'summary' => 'Implemented session RPCs',
                    'name' => 'RPC work',
                    'clientName' => 'laravel',
                    'isRemote' => false,
                    'isDetached' => false,
                    'context' => [
                        'cwd' => '/workspace',
                        'hostType' => 'github',
                    ],
                    'mcTaskId' => 'task-123',
                ]],
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->enrichMetadata(new SessionsEnrichMetadataRequest([
            new LocalSessionMetadataValue(
                sessionId: 'local-session',
                startTime: '2026-10-16T09:00:00Z',
                modifiedTime: '2026-10-17T09:00:00Z',
                isRemote: false,
                isDetached: false,
                context: new LocalSessionContext(
                    cwd: '/workspace',
                    hostType: 'github',
                ),
            ),
        ]));

        expect($result)->toBeInstanceOf(SessionsEnrichMetadataResult::class)
            ->and($result->sessions[0])->toBeInstanceOf(LocalSessionMetadataValue::class)
            ->and($result->sessions[0]->summary)->toBe('Implemented session RPCs')
            ->and($result->sessions[0]->isRemote)->toBeFalse()
            ->and($result->sessions[0]->isDetached)->toBeFalse()
            ->and($result->sessions[0]->context)->toBeInstanceOf(LocalSessionContext::class)
            ->and($result->sessions[0]->context->hostType)->toBe('github')
            ->and($result->sessions[0]->mcTaskId)->toBe('task-123');
    });
});
