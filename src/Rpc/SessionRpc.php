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
 * $session->rpc()->model()->switchTo(new ModelSwitchToRequest(modelId: 'gpt-4'));
 * $session->rpc()->mode()->get();
 * $session->rpc()->mode()->set(new ModeSetRequest(mode: 'plan'));
 * $session->rpc()->name()->get();
 * $session->rpc()->name()->set(new NameSetRequest(name: 'my session'));
 * $session->rpc()->plan()->read();
 * $session->rpc()->workspaces()->getWorkspace();
 * $session->rpc()->workspaces()->listFiles();
 * $session->rpc()->fleet()->start();
 * $session->rpc()->log()->log(new LogRequest(message: 'Processing started'));
 * $session->rpc()->log()->log(new LogRequest(message: 'Disk usage high', level: LogLevel::WARNING));
 * $session->rpc()->agent()->list();
 * $session->rpc()->agent()->reload();
 * $session->rpc()->skills()->list();
 * $session->rpc()->mcp()->list();
 * $session->rpc()->plugins()->list();
 * $session->rpc()->extensions()->list();
 * $session->rpc()->history()->compact();
 * $session->rpc()->history()->truncate(new HistoryTruncateRequest(eventId: '...'));
 * $session->rpc()->tools()->handlePendingToolCall(new ToolsHandlePendingToolCallRequest(requestId: '...', result: 'done'));
 * $session->rpc()->commands()->handlePendingCommand(new CommandsHandlePendingCommandRequest(requestId: '...'));
 * $session->rpc()->ui()->elicitation(new UIElicitationRequest(message: '...', requestedSchema: [...]));
 * $session->rpc()->permissions()->handlePendingPermissionRequest(new PermissionDecisionRequest(requestId: '...', decision: PermissionRequestResultKind::approved()));
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

    /**
     * Usage metrics RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function usage(): PendingUsage
    {
        return new PendingUsage($this->client, $this->sessionId);
    }
}
