<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpConfigAddRequest;
use Revolution\Copilot\Types\Rpc\McpConfigDisableRequest;
use Revolution\Copilot\Types\Rpc\McpConfigEnableRequest;
use Revolution\Copilot\Types\Rpc\McpConfigList;
use Revolution\Copilot\Types\Rpc\McpConfigRemoveRequest;
use Revolution\Copilot\Types\Rpc\McpConfigUpdateRequest;
use Revolution\Copilot\Types\Rpc\McpDiscoverRequest;
use Revolution\Copilot\Types\Rpc\McpDiscoverResult;

/**
 * Pending MCP configuration RPC operations (server-scoped).
 *
 * Manages MCP server configurations at the server level,
 * separate from session-scoped MCP operations.
 */
class PendingServerMcpConfig
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * List all MCP server configurations.
     */
    public function list(): McpConfigList
    {
        return McpConfigList::fromArray(
            $this->client->request('mcp.config.list', []),
        );
    }

    /**
     * Add a new MCP server configuration.
     */
    public function add(McpConfigAddRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpConfigAddRequest ? $params : McpConfigAddRequest::fromArray($params))->toArray();

        $this->client->request('mcp.config.add', $paramsArray);
    }

    /**
     * Update an existing MCP server configuration.
     */
    public function update(McpConfigUpdateRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpConfigUpdateRequest ? $params : McpConfigUpdateRequest::fromArray($params))->toArray();

        $this->client->request('mcp.config.update', $paramsArray);
    }

    /**
     * Remove an MCP server configuration.
     */
    public function remove(McpConfigRemoveRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpConfigRemoveRequest ? $params : McpConfigRemoveRequest::fromArray($params))->toArray();

        $this->client->request('mcp.config.remove', $paramsArray);
    }

    /**
     * Discover MCP servers from all sources.
     */
    public function discover(McpDiscoverRequest|array $params = []): McpDiscoverResult
    {
        $paramsArray = ($params instanceof McpDiscoverRequest ? $params : McpDiscoverRequest::fromArray($params))->toArray();

        return McpDiscoverResult::fromArray(
            $this->client->request('mcp.discover', $paramsArray),
        );
    }

    /**
     * Enable MCP servers globally (removes them from the disabled list).
     */
    public function enable(McpConfigEnableRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpConfigEnableRequest ? $params : McpConfigEnableRequest::fromArray($params))->toArray();

        $this->client->request('mcp.config.enable', $paramsArray);
    }

    /**
     * Disable MCP servers globally (adds them to the disabled list).
     */
    public function disable(McpConfigDisableRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpConfigDisableRequest ? $params : McpConfigDisableRequest::fromArray($params))->toArray();

        $this->client->request('mcp.config.disable', $paramsArray);
    }
}
