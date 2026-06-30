<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Typed session-scoped RPC methods.
 */
class SessionRpc
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Model RPC operations.
     */
    public function model(): PendingModel
    {
        return new PendingModel($this->client, $this->sessionId);
    }

    /**
     * Mode RPC operations.
     */
    public function mode(): PendingMode
    {
        return new PendingMode($this->client, $this->sessionId);
    }

    /**
     * Name RPC operations.
     */
    public function name(): PendingName
    {
        return new PendingName($this->client, $this->sessionId);
    }

    /**
     * Plan RPC operations.
     */
    public function plan(): PendingPlan
    {
        return new PendingPlan($this->client, $this->sessionId);
    }

    /**
     * Workspaces RPC operations.
     */
    public function workspaces(): PendingWorkspaces
    {
        return new PendingWorkspaces($this->client, $this->sessionId);
    }

    /**
     * Instructions RPC operations.
     */
    public function instructions(): PendingInstructions
    {
        return new PendingInstructions($this->client, $this->sessionId);
    }

    /**
     * Fleet RPC operations.
     */
    public function fleet(): PendingFleet
    {
        return new PendingFleet($this->client, $this->sessionId);
    }

    /**
     * Agent RPC operations.
     */
    public function agent(): PendingAgent
    {
        return new PendingAgent($this->client, $this->sessionId);
    }

    /**
     * Skills RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function skills(): PendingSkills
    {
        return new PendingSkills($this->client, $this->sessionId);
    }

    /**
     * MCP server RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function mcp(): PendingMcp
    {
        return new PendingMcp($this->client, $this->sessionId);
    }

    /**
     * Plugins RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function plugins(): PendingPlugins
    {
        return new PendingPlugins($this->client, $this->sessionId);
    }

    /**
     * Extensions RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function extensions(): PendingExtensions
    {
        return new PendingExtensions($this->client, $this->sessionId);
    }

    /**
     * History RPC operations (compaction, truncation).
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function history(): PendingHistory
    {
        return new PendingHistory($this->client, $this->sessionId);
    }

    /**
     * Session-scoped tools RPC operations (protocol v3+).
     *
     * Used to respond to tool call requests received as session events.
     */
    public function tools(): PendingTools
    {
        return new PendingTools($this->client, $this->sessionId);
    }

    /**
     * Session-scoped permissions RPC operations (protocol v3+).
     *
     * Used to respond to permission requests received as session events.
     */
    public function permissions(): PendingPermissions
    {
        return new PendingPermissions($this->client, $this->sessionId);
    }

    /**
     * Session-scoped commands RPC operations.
     *
     * Used to respond to command invocation events.
     */
    public function commands(): PendingCommands
    {
        return new PendingCommands($this->client, $this->sessionId);
    }

    /**
     * UI RPC operations.
     *
     * Used to respond to UI elicitation requests.
     */
    public function ui(): PendingUi
    {
        return new PendingUi($this->client, $this->sessionId);
    }

    /**
     * Completions RPC operations.
     *
     * Used to get host-driven completion items for the composer input.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function completions(): PendingCompletions
    {
        return new PendingCompletions($this->client, $this->sessionId);
    }

    /**
     * Session log RPC operations.
     *
     * Used to log messages to the session timeline.
     */
    public function log(): PendingLog
    {
        return new PendingLog($this->client, $this->sessionId);
    }

    /**
     * Metadata RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function metadata(): PendingMetadata
    {
        return new PendingMetadata($this->client, $this->sessionId);
    }

    /**
     * Event log RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function eventLog(): PendingEventLog
    {
        return new PendingEventLog($this->client, $this->sessionId);
    }

    /**
     * Shell RPC operations.
     *
     * Used to execute and manage shell commands within the session.
     */
    public function shell(): PendingShell
    {
        return new PendingShell($this->client, $this->sessionId);
    }

    /**
     * Usage metrics RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function usage(): PendingUsage
    {
        return new PendingUsage($this->client, $this->sessionId);
    }

    /**
     * Session GitHub authentication RPC operations.
     */
    public function gitHubAuth(): PendingSessionAuth
    {
        return new PendingSessionAuth($this->client, $this->sessionId);
    }

    /**
     * Provider RPC operations.
     *
     * Returns the provider endpoint and credentials the session is currently configured to use.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function provider(): PendingProvider
    {
        return new PendingProvider($this->client, $this->sessionId);
    }

    /**
     * Tasks RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function tasks(): PendingTasks
    {
        return new PendingTasks($this->client, $this->sessionId);
    }

    /**
     * Remote session RPC operations (Mission Control integration).
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function remote(): PendingRemote
    {
        return new PendingRemote($this->client, $this->sessionId);
    }

    /**
     * Session visibility RPC operations (Mission Control sharing status).
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function visibility(): PendingVisibility
    {
        return new PendingVisibility($this->client, $this->sessionId);
    }

    /**
     * MCP headers RPC operations (dynamic headers refresh).
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function mcpHeaders(): PendingMcpHeaders
    {
        return new PendingMcpHeaders($this->client, $this->sessionId);
    }

    /**
     * Queue RPC operations.
     *
     * Used to inspect and manage pending queued items in the session.
     */
    public function queue(): PendingQueue
    {
        return new PendingQueue($this->client, $this->sessionId);
    }

    /**
     * Schedule RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function schedule(): PendingSchedule
    {
        return new PendingSchedule($this->client, $this->sessionId);
    }

    /**
     * Suspend the current session.
     *
     * Suspends the session, pausing its execution until resumed.
     */
    public function suspend(): void
    {
        $this->client->request('session.suspend', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
