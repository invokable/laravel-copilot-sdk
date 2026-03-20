<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionMcpDisableParams;
use Revolution\Copilot\Types\Rpc\SessionMcpEnableParams;
use Revolution\Copilot\Types\Rpc\SessionMcpListResult;

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
    public function list(): SessionMcpListResult
    {
        return SessionMcpListResult::fromArray(
            $this->client->request('session.mcp.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Enable an MCP server.
     */
    public function enable(SessionMcpEnableParams|array $params): array
    {
        $paramsArray = ($params instanceof SessionMcpEnableParams ? $params : SessionMcpEnableParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.mcp.enable', $paramsArray);
    }

    /**
     * Disable an MCP server.
     */
    public function disable(SessionMcpDisableParams|array $params): array
    {
        $paramsArray = ($params instanceof SessionMcpDisableParams ? $params : SessionMcpDisableParams::fromArray($params))->toArray();
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
