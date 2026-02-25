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
 * $session->rpc()->agent()->list();
 * $session->rpc()->compaction()->compact();
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
}
