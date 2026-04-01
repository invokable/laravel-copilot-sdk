<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpConfigAddParams;
use Revolution\Copilot\Types\Rpc\McpConfigListResult;
use Revolution\Copilot\Types\Rpc\McpConfigRemoveParams;
use Revolution\Copilot\Types\Rpc\McpConfigUpdateParams;

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
    public function list(): McpConfigListResult
    {
        return McpConfigListResult::fromArray(
            $this->client->request('mcp.config.list', []),
        );
    }

    /**
     * Add a new MCP server configuration.
     */
    public function add(McpConfigAddParams|array $params): void
    {
        $paramsArray = ($params instanceof McpConfigAddParams ? $params : McpConfigAddParams::fromArray($params))->toArray();

        $this->client->request('mcp.config.add', $paramsArray);
    }

    /**
     * Update an existing MCP server configuration.
     */
    public function update(McpConfigUpdateParams|array $params): void
    {
        $paramsArray = ($params instanceof McpConfigUpdateParams ? $params : McpConfigUpdateParams::fromArray($params))->toArray();

        $this->client->request('mcp.config.update', $paramsArray);
    }

    /**
     * Remove an MCP server configuration.
     */
    public function remove(McpConfigRemoveParams|array $params): void
    {
        $paramsArray = ($params instanceof McpConfigRemoveParams ? $params : McpConfigRemoveParams::fromArray($params))->toArray();

        $this->client->request('mcp.config.remove', $paramsArray);
    }
}
