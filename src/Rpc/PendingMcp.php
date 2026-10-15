<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpCancelSamplingExecutionParams;
use Revolution\Copilot\Types\Rpc\McpCancelSamplingExecutionResult;
use Revolution\Copilot\Types\Rpc\McpDisableRequest;
use Revolution\Copilot\Types\Rpc\McpEnableRequest;
use Revolution\Copilot\Types\Rpc\McpExecuteSamplingParams;
use Revolution\Copilot\Types\Rpc\McpIsServerRunningRequest;
use Revolution\Copilot\Types\Rpc\McpIsServerRunningResult;
use Revolution\Copilot\Types\Rpc\McpListToolsRequest;
use Revolution\Copilot\Types\Rpc\McpListToolsResult;
use Revolution\Copilot\Types\Rpc\McpReloadWithConfigRequest;
use Revolution\Copilot\Types\Rpc\McpOauthAuthenticationStateChangedRequest;
use Revolution\Copilot\Types\Rpc\McpOauthCompleteRequest;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingRequest;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingResult;
use Revolution\Copilot\Types\Rpc\McpOauthLoginRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginResult;
use Revolution\Copilot\Types\Rpc\McpOauthRespondRequest;
use Revolution\Copilot\Types\Rpc\McpOauthRespondResult;
use Revolution\Copilot\Types\Rpc\McpPromptsGetRequest;
use Revolution\Copilot\Types\Rpc\McpPromptsGetResult;
use Revolution\Copilot\Types\Rpc\McpPromptsListRequest;
use Revolution\Copilot\Types\Rpc\McpPromptsListResult;
use Revolution\Copilot\Types\Rpc\McpRemoveGitHubResult;
use Revolution\Copilot\Types\Rpc\McpRestartServerRequest;
use Revolution\Copilot\Types\Rpc\McpSamplingExecutionResult;
use Revolution\Copilot\Types\Rpc\McpServerList;
use Revolution\Copilot\Types\Rpc\McpSetEnvValueModeParams;
use Revolution\Copilot\Types\Rpc\McpSetEnvValueModeResult;
use Revolution\Copilot\Types\Rpc\McpStartServerRequest;
use Revolution\Copilot\Types\Rpc\McpStopServerRequest;
use Revolution\Copilot\Types\Rpc\MoveMcpLoadingToBackgroundResult;

/**
 * Pending MCP server RPC operations for a session.
 *
 * External client registration is intentionally not exposed here: the upstream
 * API accepts in-process MCP client, transport, and config instances that
 * cannot be represented by Laravel's JSON-RPC transport.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingMcp
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List configured MCP servers.
     */
    public function list(): McpServerList
    {
        return McpServerList::fromArray(
            $this->client->request('session.mcp.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * List configured MCP servers with their runtime connection state.
     */
    public function listConfigured(): array
    {
        return $this->client->request('session.mcp.listConfigured', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Publish or clear the IDE context associated with this session.
     */
    public function setConnectedIdeInfo(array $params): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request('session.mcp.setConnectedIdeInfo', $params);
    }

    /**
     * Enable an MCP server.
     */
    public function enable(McpEnableRequest|array $params): array
    {
        $paramsArray = ($params instanceof McpEnableRequest ? $params : McpEnableRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.mcp.enable', $paramsArray);
    }

    /**
     * Disable an MCP server.
     */
    public function disable(McpDisableRequest|array $params): array
    {
        $paramsArray = ($params instanceof McpDisableRequest ? $params : McpDisableRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.mcp.disable', $paramsArray);
    }

    /**
     * Reload MCP servers.
     */
    public function reload(): array
    {
        return $this->client->request('session.mcp.reload', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Reload MCP server connections with an explicit host-provided configuration.
     *
     * @return array<string, mixed>
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function reloadWithConfig(McpReloadWithConfigRequest|array $params): array
    {
        $paramsArray = ($params instanceof McpReloadWithConfigRequest
            ? $params
            : McpReloadWithConfigRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.mcp.reloadWithConfig', $paramsArray);
    }

    /**
     * Releases any turns waiting on an in-flight MCP load without cancelling the load,
     * letting the agent proceed while MCP servers finish connecting in the background.
     *
     * No-op when no MCP load is in flight or waiting turns were already released.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function moveLoadingToBackground(): MoveMcpLoadingToBackgroundResult
    {
        return MoveMcpLoadingToBackgroundResult::fromArray(
            $this->client->request('session.mcp.moveLoadingToBackground', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /** MCP OAuth RPC operations. */
    public function oauth(): PendingMcpOauth
    {
        return new PendingMcpOauth($this->client, $this->sessionId);
    }

    /**
     * Resolves a pending MCP OAuth request with a host-provided token or cancellation.
     *
     * The pending request is emitted as mcp.oauth_required with the data necessary
     * to authorize the request.
     */
    public function handlePendingRequest(McpOauthHandlePendingRequest|array $params): McpOauthHandlePendingResult
    {
        return $this->oauth()->handlePendingRequest($params);
    }

    /**
     * Notify the session that persisted MCP OAuth credentials changed.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function authenticationStateChanged(McpOauthAuthenticationStateChangedRequest|array $params = []): void
    {
        $this->oauth()->authenticationStateChanged($params);
    }

    /**
     * Initiate MCP OAuth login for a remote server.
     */
    public function login(McpOauthLoginRequest|array $params): McpOauthLoginResult
    {
        return $this->oauth()->login($params);
    }

    /**
     * Resolves a pending MCP OAuth request emitted as mcp.oauth_required.
     */
    public function respond(McpOauthRespondRequest|array $params): McpOauthRespondResult
    {
        return $this->oauth()->respond($params);
    }

    /** Complete an MCP OAuth login through a host-managed redirect callback. */
    public function complete(McpOauthCompleteRequest|array $params): void
    {
        $this->oauth()->complete($params);
    }

    /**
     * Start a specific MCP server on this session.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function startServer(McpStartServerRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpStartServerRequest ? $params : McpStartServerRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.mcp.startServer', $paramsArray);
    }

    /**
     * Restart a specific MCP server on this session.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function restartServer(McpRestartServerRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpRestartServerRequest ? $params : McpRestartServerRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.mcp.restartServer', $paramsArray);
    }

    /**
     * Remove the auto-managed GitHub MCP server when present.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function removeGitHub(): McpRemoveGitHubResult
    {
        return McpRemoveGitHubResult::fromArray(
            $this->client->request('session.mcp.removeGitHub', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Set how MCP server environment values are resolved.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function setEnvValueMode(McpSetEnvValueModeParams|array $params): McpSetEnvValueModeResult
    {
        $paramsArray = ($params instanceof McpSetEnvValueModeParams ? $params : McpSetEnvValueModeParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpSetEnvValueModeResult::fromArray(
            $this->client->request('session.mcp.setEnvValueMode', $paramsArray),
        );
    }

    /**
     * Execute an MCP sampling inference request.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function executeSampling(McpExecuteSamplingParams|array $params): McpSamplingExecutionResult
    {
        $paramsArray = ($params instanceof McpExecuteSamplingParams ? $params : McpExecuteSamplingParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpSamplingExecutionResult::fromArray(
            $this->client->request('session.mcp.executeSampling', $paramsArray),
        );
    }

    /**
     * Cancel an in-flight MCP sampling execution.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function cancelSamplingExecution(McpCancelSamplingExecutionParams|array $params): McpCancelSamplingExecutionResult
    {
        $paramsArray = ($params instanceof McpCancelSamplingExecutionParams ? $params : McpCancelSamplingExecutionParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpCancelSamplingExecutionResult::fromArray(
            $this->client->request('session.mcp.cancelSamplingExecution', $paramsArray),
        );
    }

    /**
     * List tools exposed by a connected MCP server.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function listTools(McpListToolsRequest|array $params): McpListToolsResult
    {
        $paramsArray = ($params instanceof McpListToolsRequest ? $params : McpListToolsRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpListToolsResult::fromArray(
            $this->client->request('session.mcp.listTools', $paramsArray),
        );
    }

    /**
     * Stop a specific MCP server.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function stopServer(McpStopServerRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpStopServerRequest ? $params : McpStopServerRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.mcp.stopServer', $paramsArray);
    }

    /**
     * Check whether a specific MCP server is currently running.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function isServerRunning(McpIsServerRunningRequest|array $params): McpIsServerRunningResult
    {
        $paramsArray = ($params instanceof McpIsServerRunningRequest ? $params : McpIsServerRunningRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpIsServerRunningResult::fromArray(
            $this->client->request('session.mcp.isServerRunning', $paramsArray),
        );
    }

    /** List prompts advertised by an MCP server. */
    public function listPrompts(McpPromptsListRequest|array $params): McpPromptsListResult
    {
        return $this->prompts()->list($params);
    }

    /** Fetch a rendered prompt from an MCP server without sending it to the model. */
    public function getPrompt(McpPromptsGetRequest|array $params): McpPromptsGetResult
    {
        return $this->prompts()->get($params);
    }

    /**
     * MCP resources RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function resources(): PendingMcpResources
    {
        return new PendingMcpResources($this->client, $this->sessionId);
    }

    /**
     * MCP Apps RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function apps(): PendingMcpApps
    {
        return new PendingMcpApps($this->client, $this->sessionId);
    }

    /** Prompt RPC operations. */
    public function prompts(): PendingMcpPrompts
    {
        return new PendingMcpPrompts($this->client, $this->sessionId);
    }
}
