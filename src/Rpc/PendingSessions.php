<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\EventsReadResult;
use Revolution\Copilot\Types\Rpc\RemoteControlStatusResult;
use Revolution\Copilot\Types\Rpc\RemoteControlStopResult;
use Revolution\Copilot\Types\Rpc\RemoteControlTransferResult;
use Revolution\Copilot\Types\Rpc\SessionBulkDeleteResult;
use Revolution\Copilot\Types\Rpc\SessionList;
use Revolution\Copilot\Types\Rpc\SessionLoadDeferredRepoHooksResult;
use Revolution\Copilot\Types\Rpc\SessionPruneResult;
use Revolution\Copilot\Types\Rpc\SessionsBulkDeleteRequest;
use Revolution\Copilot\Types\Rpc\SessionsCheckInUseRequest;
use Revolution\Copilot\Types\Rpc\SessionsCheckInUseResult;
use Revolution\Copilot\Types\Rpc\SessionsCloseRequest;
use Revolution\Copilot\Types\Rpc\SessionsCloseResult;
use Revolution\Copilot\Types\Rpc\SessionsCreateWorkspaceRequest;
use Revolution\Copilot\Types\Rpc\SessionsCreateWorkspaceResult;
use Revolution\Copilot\Types\Rpc\SessionsEnrichMetadataRequest;
use Revolution\Copilot\Types\Rpc\SessionsEnrichMetadataResult;
use Revolution\Copilot\Types\Rpc\SessionsFindByPrefixRequest;
use Revolution\Copilot\Types\Rpc\SessionsFindByPrefixResult;
use Revolution\Copilot\Types\Rpc\SessionsFindByTaskIdRequest;
use Revolution\Copilot\Types\Rpc\SessionsFindByTaskIdResult;
use Revolution\Copilot\Types\Rpc\SessionsForkRequest;
use Revolution\Copilot\Types\Rpc\SessionsForkResult;
use Revolution\Copilot\Types\Rpc\SessionsGetClientMetadataRequest;
use Revolution\Copilot\Types\Rpc\SessionsGetLastForContextRequest;
use Revolution\Copilot\Types\Rpc\SessionsGetLastForContextResult;
use Revolution\Copilot\Types\Rpc\SessionSizes;
use Revolution\Copilot\Types\Rpc\SessionsListRequest;
use Revolution\Copilot\Types\Rpc\SessionsLoadDeferredRepoHooksRequest;
use Revolution\Copilot\Types\Rpc\SessionsLoadWorkspaceRequest;
use Revolution\Copilot\Types\Rpc\SessionsLoadWorkspaceResult;
use Revolution\Copilot\Types\Rpc\SessionsPruneOldRequest;
use Revolution\Copilot\Types\Rpc\SessionsReadPersistedEventsRequest;
use Revolution\Copilot\Types\Rpc\SessionsReleaseLockRequest;
use Revolution\Copilot\Types\Rpc\SessionsReleaseLockResult;
use Revolution\Copilot\Types\Rpc\SessionsReloadPluginHooksRequest;
use Revolution\Copilot\Types\Rpc\SessionsReloadPluginHooksResult;
use Revolution\Copilot\Types\Rpc\SessionsSaveRequest;
use Revolution\Copilot\Types\Rpc\SessionsSaveResult;
use Revolution\Copilot\Types\Rpc\SessionsSetAdditionalPluginsRequest;
use Revolution\Copilot\Types\Rpc\SessionsSetAdditionalPluginsResult;
use Revolution\Copilot\Types\Rpc\SessionsSetRemoteControlSteeringRequest;
use Revolution\Copilot\Types\Rpc\SessionsStartRemoteControlRequest;
use Revolution\Copilot\Types\Rpc\SessionsStopRemoteControlRequest;
use Revolution\Copilot\Types\Rpc\SessionsTransferRemoteControlRequest;
use Revolution\Copilot\Types\Rpc\SessionsUpdateWorkspaceFieldsRequest;

/**
 * Pending sessions RPC operations (server-scoped).
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSessions
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * List persisted sessions without opening them.
     */
    public function list(SessionsListRequest|array $params = []): SessionList
    {
        $paramsArray = ($params instanceof SessionsListRequest ? $params : SessionsListRequest::fromArray($params))->toArray();

        return SessionList::fromArray(
            $this->client->request('sessions.list', $paramsArray),
        );
    }

    /**
     * Read persisted events from a session journal.
     */
    public function readPersistedEvents(SessionsReadPersistedEventsRequest|array $params): EventsReadResult
    {
        $paramsArray = ($params instanceof SessionsReadPersistedEventsRequest
            ? $params
            : SessionsReadPersistedEventsRequest::fromArray($params))->toArray();

        return EventsReadResult::fromArray(
            $this->client->request('sessions.readPersistedEvents', $paramsArray),
        );
    }

    /**
     * Find a persisted session by its GitHub task ID.
     */
    public function findByTaskId(SessionsFindByTaskIdRequest|array $params): SessionsFindByTaskIdResult
    {
        $paramsArray = ($params instanceof SessionsFindByTaskIdRequest
            ? $params
            : SessionsFindByTaskIdRequest::fromArray($params))->toArray();

        return SessionsFindByTaskIdResult::fromArray(
            $this->client->request('sessions.findByTaskId', $paramsArray),
        );
    }

    /**
     * Find a persisted session by a unique UUID prefix.
     */
    public function findByPrefix(SessionsFindByPrefixRequest|array $params): SessionsFindByPrefixResult
    {
        $paramsArray = ($params instanceof SessionsFindByPrefixRequest
            ? $params
            : SessionsFindByPrefixRequest::fromArray($params))->toArray();

        return SessionsFindByPrefixResult::fromArray(
            $this->client->request('sessions.findByPrefix', $paramsArray),
        );
    }

    /**
     * Find the most relevant persisted session for a context.
     */
    public function getLastForContext(SessionsGetLastForContextRequest|array $params = []): SessionsGetLastForContextResult
    {
        $paramsArray = ($params instanceof SessionsGetLastForContextRequest
            ? $params
            : SessionsGetLastForContextRequest::fromArray($params))->toArray();

        return SessionsGetLastForContextResult::fromArray(
            $this->client->request('sessions.getLastForContext', $paramsArray),
        );
    }

    /**
     * Get the on-disk workspace size of persisted sessions.
     */
    public function getSizes(): SessionSizes
    {
        return SessionSizes::fromArray(
            $this->client->request('sessions.getSizes', []),
        );
    }

    /**
     * Check which persisted sessions are currently in use by another process.
     */
    public function checkInUse(SessionsCheckInUseRequest|array $params): SessionsCheckInUseResult
    {
        $paramsArray = ($params instanceof SessionsCheckInUseRequest
            ? $params
            : SessionsCheckInUseRequest::fromArray($params))->toArray();

        return SessionsCheckInUseResult::fromArray(
            $this->client->request('sessions.checkInUse', $paramsArray),
        );
    }

    /**
     * Delete multiple persisted sessions.
     */
    public function bulkDelete(SessionsBulkDeleteRequest|array $params): SessionBulkDeleteResult
    {
        $paramsArray = ($params instanceof SessionsBulkDeleteRequest
            ? $params
            : SessionsBulkDeleteRequest::fromArray($params))->toArray();

        return SessionBulkDeleteResult::fromArray(
            $this->client->request('sessions.bulkDelete', $paramsArray),
        );
    }

    /**
     * Delete persisted sessions older than the requested age.
     */
    public function pruneOld(SessionsPruneOldRequest|array $params): SessionPruneResult
    {
        $paramsArray = ($params instanceof SessionsPruneOldRequest
            ? $params
            : SessionsPruneOldRequest::fromArray($params))->toArray();

        return SessionPruneResult::fromArray(
            $this->client->request('sessions.pruneOld', $paramsArray),
        );
    }

    /**
     * Close an active session, flushing events and releasing its in-use lock.
     */
    public function close(SessionsCloseRequest|array $params): SessionsCloseResult
    {
        $paramsArray = ($params instanceof SessionsCloseRequest
            ? $params
            : SessionsCloseRequest::fromArray($params))->toArray();

        return SessionsCloseResult::fromArray(
            $this->client->request('sessions.close', $paramsArray),
        );
    }

    /**
     * Flush a session's pending events to disk.
     */
    public function save(SessionsSaveRequest|array $params): SessionsSaveResult
    {
        $paramsArray = ($params instanceof SessionsSaveRequest
            ? $params
            : SessionsSaveRequest::fromArray($params))->toArray();

        return SessionsSaveResult::fromArray(
            $this->client->request('sessions.save', $paramsArray),
        );
    }

    /**
     * Release this process's in-use lock for a session.
     */
    public function releaseLock(SessionsReleaseLockRequest|array $params): SessionsReleaseLockResult
    {
        $paramsArray = ($params instanceof SessionsReleaseLockRequest
            ? $params
            : SessionsReleaseLockRequest::fromArray($params))->toArray();

        return SessionsReleaseLockResult::fromArray(
            $this->client->request('sessions.releaseLock', $paramsArray),
        );
    }

    /**
     * Reload user and plugin hooks, optionally deferring repository hooks.
     */
    public function reloadPluginHooks(SessionsReloadPluginHooksRequest|array $params): SessionsReloadPluginHooksResult
    {
        $paramsArray = ($params instanceof SessionsReloadPluginHooksRequest
            ? $params
            : SessionsReloadPluginHooksRequest::fromArray($params))->toArray();

        return SessionsReloadPluginHooksResult::fromArray(
            $this->client->request('sessions.reloadPluginHooks', $paramsArray),
        );
    }

    /**
     * Load repository hooks that were deferred until the folder was trusted.
     */
    public function loadDeferredRepoHooks(SessionsLoadDeferredRepoHooksRequest|array $params): SessionLoadDeferredRepoHooksResult
    {
        $paramsArray = ($params instanceof SessionsLoadDeferredRepoHooksRequest
            ? $params
            : SessionsLoadDeferredRepoHooksRequest::fromArray($params))->toArray();

        return SessionLoadDeferredRepoHooksResult::fromArray(
            $this->client->request('sessions.loadDeferredRepoHooks', $paramsArray),
        );
    }

    /**
     * Replace the session manager's additional installed plugins.
     */
    public function setAdditionalPlugins(SessionsSetAdditionalPluginsRequest|array $params): SessionsSetAdditionalPluginsResult
    {
        $paramsArray = ($params instanceof SessionsSetAdditionalPluginsRequest
            ? $params
            : SessionsSetAdditionalPluginsRequest::fromArray($params))->toArray();

        return SessionsSetAdditionalPluginsResult::fromArray(
            $this->client->request('sessions.setAdditionalPlugins', $paramsArray),
        );
    }

    /**
     * Backfill missing summary and context fields on local session metadata.
     */
    public function enrichMetadata(SessionsEnrichMetadataRequest|array $params): SessionsEnrichMetadataResult
    {
        $paramsArray = ($params instanceof SessionsEnrichMetadataRequest
            ? $params
            : SessionsEnrichMetadataRequest::fromArray($params))->toArray();

        return SessionsEnrichMetadataResult::fromArray(
            $this->client->request('sessions.enrichMetadata', $paramsArray),
        );
    }

    /**
     * Fork a session.
     *
     * Creates a new session that includes events from the source session.
     * Optionally, provide a toEventId to include only events before that ID.
     */
    public function fork(SessionsForkRequest|array $params): SessionsForkResult
    {
        $paramsArray = ($params instanceof SessionsForkRequest ? $params : SessionsForkRequest::fromArray($params))->toArray();

        return SessionsForkResult::fromArray(
            $this->client->request('sessions.fork', $paramsArray),
        );
    }

    /**
     * Read client-owned metadata for multiple persisted local sessions without opening them.
     * Results preserve request order and report missing, corrupt, unsupported, or temporarily
     * unavailable sessions independently.
     *
     * @return array<int, array<string, mixed>> Ordered client metadata outcomes for the requested local sessions.
     */
    public function getClientMetadata(SessionsGetClientMetadataRequest|array $params): array
    {
        $paramsArray = ($params instanceof SessionsGetClientMetadataRequest ? $params : SessionsGetClientMetadataRequest::fromArray($params))->toArray();

        return $this->client->request('sessions.getClientMetadata', $paramsArray);
    }

    /**
     * Create a persisted workspace record before a session is opened.
     */
    public function createWorkspace(SessionsCreateWorkspaceRequest|array $params): SessionsCreateWorkspaceResult
    {
        $paramsArray = ($params instanceof SessionsCreateWorkspaceRequest
            ? $params
            : SessionsCreateWorkspaceRequest::fromArray($params))->toArray();

        return SessionsCreateWorkspaceResult::fromArray(
            $this->client->request('sessions.createWorkspace', $paramsArray),
        );
    }

    /**
     * Read a persisted workspace record without opening its session.
     */
    public function loadWorkspace(SessionsLoadWorkspaceRequest|array $params): SessionsLoadWorkspaceResult
    {
        $paramsArray = ($params instanceof SessionsLoadWorkspaceRequest
            ? $params
            : SessionsLoadWorkspaceRequest::fromArray($params))->toArray();

        return SessionsLoadWorkspaceResult::fromArray(
            $this->client->request('sessions.loadWorkspace', $paramsArray),
        );
    }

    /**
     * Merge fields into a persisted workspace record.
     */
    public function updateWorkspaceFields(SessionsUpdateWorkspaceFieldsRequest|array $params): array
    {
        $paramsArray = ($params instanceof SessionsUpdateWorkspaceFieldsRequest
            ? $params
            : SessionsUpdateWorkspaceFieldsRequest::fromArray($params))->toArray();

        return $this->client->request('sessions.updateWorkspaceFields', $paramsArray);
    }

    /**
     * Attach runtime-managed remote control to a session.
     */
    public function startRemoteControl(SessionsStartRemoteControlRequest|array $params): RemoteControlStatusResult
    {
        $paramsArray = ($params instanceof SessionsStartRemoteControlRequest
            ? $params
            : SessionsStartRemoteControlRequest::fromArray($params))->toArray();

        return RemoteControlStatusResult::fromArray(
            $this->client->request('sessions.startRemoteControl', $paramsArray),
        );
    }

    /**
     * Stop runtime-managed remote control.
     */
    public function stopRemoteControl(SessionsStopRemoteControlRequest|array $params = []): RemoteControlStopResult
    {
        $paramsArray = ($params instanceof SessionsStopRemoteControlRequest
            ? $params
            : SessionsStopRemoteControlRequest::fromArray($params))->toArray();

        return RemoteControlStopResult::fromArray(
            $this->client->request('sessions.stopRemoteControl', $paramsArray),
        );
    }

    /**
     * Transfer runtime-managed remote control to another session.
     */
    public function transferRemoteControl(SessionsTransferRemoteControlRequest|array $params): RemoteControlTransferResult
    {
        $paramsArray = ($params instanceof SessionsTransferRemoteControlRequest
            ? $params
            : SessionsTransferRemoteControlRequest::fromArray($params))->toArray();

        return RemoteControlTransferResult::fromArray(
            $this->client->request('sessions.transferRemoteControl', $paramsArray),
        );
    }

    /**
     * Enable or disable runtime-managed remote steering.
     */
    public function setRemoteControlSteering(SessionsSetRemoteControlSteeringRequest|array $params): RemoteControlStatusResult
    {
        $paramsArray = ($params instanceof SessionsSetRemoteControlSteeringRequest
            ? $params
            : SessionsSetRemoteControlSteeringRequest::fromArray($params))->toArray();

        return RemoteControlStatusResult::fromArray(
            $this->client->request('sessions.setRemoteControlSteering', $paramsArray),
        );
    }

    /**
     * Get the current runtime-managed remote-control status.
     */
    public function getRemoteControlStatus(): RemoteControlStatusResult
    {
        return RemoteControlStatusResult::fromArray(
            $this->client->request('sessions.getRemoteControlStatus', []),
        );
    }
}
