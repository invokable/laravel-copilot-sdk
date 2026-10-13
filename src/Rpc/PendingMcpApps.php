<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpAppsCallToolRequest;
use Revolution\Copilot\Types\Rpc\MCPAppsDiagnoseRequest;
use Revolution\Copilot\Types\Rpc\McpAppsDiagnoseResult;
use Revolution\Copilot\Types\Rpc\McpAppsHostContext;
use Revolution\Copilot\Types\Rpc\MCPAppsListToolsRequest;
use Revolution\Copilot\Types\Rpc\McpAppsListToolsResult;
use Revolution\Copilot\Types\Rpc\McpAppsReadResourceRequest;
use Revolution\Copilot\Types\Rpc\McpAppsReadResourceResult;
use Revolution\Copilot\Types\Rpc\McpAppsSetHostContextRequest;

/**
 * MCP Apps RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingMcpApps
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List the tools an MCP App view is allowed to call.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function listTools(MCPAppsListToolsRequest|array $params): McpAppsListToolsResult
    {
        $paramsArray = ($params instanceof MCPAppsListToolsRequest ? $params : MCPAppsListToolsRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpAppsListToolsResult::fromArray(
            $this->client->request('session.mcp.apps.listTools', $paramsArray),
        );
    }

    /**
     * Call a tool from an MCP App view.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function callTool(McpAppsCallToolRequest|array $params): mixed
    {
        $paramsArray = ($params instanceof McpAppsCallToolRequest ? $params : McpAppsCallToolRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.mcp.apps.callTool', $paramsArray);
    }

    /**
     * Fetch an MCP App resource from a connected server.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function readResource(McpAppsReadResourceRequest|array $params): McpAppsReadResourceResult
    {
        $paramsArray = ($params instanceof McpAppsReadResourceRequest ? $params : McpAppsReadResourceRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpAppsReadResourceResult::fromArray(
            $this->client->request('session.mcp.apps.readResource', $paramsArray),
        );
    }

    /**
     * Read the host context advertised to MCP App guests.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function getHostContext(): McpAppsHostContext
    {
        return McpAppsHostContext::fromArray(
            $this->client->request('session.mcp.apps.getHostContext', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Replace the host context advertised to MCP App guests.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function setHostContext(McpAppsSetHostContextRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpAppsSetHostContextRequest ? $params : McpAppsSetHostContextRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.mcp.apps.setHostContext', $paramsArray);
    }

    /**
     * Diagnose MCP Apps wiring for a specific server.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function diagnose(MCPAppsDiagnoseRequest|array $params): McpAppsDiagnoseResult
    {
        $paramsArray = ($params instanceof MCPAppsDiagnoseRequest ? $params : MCPAppsDiagnoseRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpAppsDiagnoseResult::fromArray(
            $this->client->request('session.mcp.apps.diagnose', $paramsArray),
        );
    }
}
