<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Types\Rpc\McpResourcesListRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesListResult;
use Revolution\Copilot\Types\Rpc\McpResourcesListTemplatesRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesListTemplatesResult;
use Revolution\Copilot\Types\Rpc\McpResourcesReadRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesReadResult;

/**
 * MCP resources RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingMcpResources
{
    public function __construct(
        protected CopilotClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Fetch an MCP resource from a connected server by URI.
     *
     * Proxies MCP `resources/read`.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function read(McpResourcesReadRequest|array $params): McpResourcesReadResult
    {
        $paramsArray = ($params instanceof McpResourcesReadRequest ? $params : McpResourcesReadRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpResourcesReadResult::fromArray(
            $this->client->request('session.mcp.resources.read', $paramsArray),
        );
    }

    /**
     * Enumerate one page of resources a connected MCP server exposes.
     *
     * Proxies MCP `resources/list`. Pass `cursor` to continue from a prior result's `nextCursor`.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function list(McpResourcesListRequest|array $params): McpResourcesListResult
    {
        $paramsArray = ($params instanceof McpResourcesListRequest ? $params : McpResourcesListRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpResourcesListResult::fromArray(
            $this->client->request('session.mcp.resources.list', $paramsArray),
        );
    }

    /**
     * Enumerate one page of resource templates a connected MCP server exposes.
     *
     * Proxies MCP `resources/templates/list`.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function listTemplates(McpResourcesListTemplatesRequest|array $params): McpResourcesListTemplatesResult
    {
        $paramsArray = ($params instanceof McpResourcesListTemplatesRequest ? $params : McpResourcesListTemplatesRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpResourcesListTemplatesResult::fromArray(
            $this->client->request('session.mcp.resources.listTemplates', $paramsArray),
        );
    }
}
