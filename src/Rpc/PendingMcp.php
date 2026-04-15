<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpDisableRequest;
use Revolution\Copilot\Types\Rpc\McpEnableRequest;
use Revolution\Copilot\Types\Rpc\McpServerList;

/**
 * Pending MCP server RPC operations for a session.
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
}
