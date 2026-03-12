<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Typed session-scoped RPC methods.
 *
 * Usage:
 * ```php
 * $session->rpc()->model()->getCurrent();
 * $session->rpc()->model()->switchTo(new SessionModelSwitchToParams(modelId: 'gpt-4'));
 * $session->rpc()->mode()->get();
 * $session->rpc()->mode()->set(new SessionModeSetParams(mode: 'plan'));
 * $session->rpc()->plan()->read();
 * $session->rpc()->workspace()->listFiles();
 * $session->rpc()->fleet()->start();
 * $session->rpc()->log()->log(new SessionLogParams(message: 'Processing started'));
 * $session->rpc()->log()->log(new SessionLogParams(message: 'Disk usage high', level: LogLevel::WARNING));
 * $session->rpc()->agent()->list();
 * $session->rpc()->compaction()->compact();
 * $session->rpc()->tools()->handlePendingToolCall(new SessionToolsHandlePendingToolCallParams(requestId: '...', result: 'done'));
 * $session->rpc()->permissions()->handlePendingPermissionRequest(new SessionPermissionsHandlePendingPermissionRequestParams(requestId: '...', result: PermissionRequestResultKind::approved()));
 * ```
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
     * Plan RPC operations.
     */
    public function plan(): PendingPlan
    {
        return new PendingPlan($this->client, $this->sessionId);
    }

    /**
     * Workspace RPC operations.
     */
    public function workspace(): PendingWorkspace
    {
        return new PendingWorkspace($this->client, $this->sessionId);
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
     * Compaction RPC operations.
     */
    public function compaction(): PendingCompaction
    {
        return new PendingCompaction($this->client, $this->sessionId);
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
     * Session log RPC operations.
     *
     * Used to log messages to the session timeline.
     */
    public function log(): PendingLog
    {
        return new PendingLog($this->client, $this->sessionId);
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
}
