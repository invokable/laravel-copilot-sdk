<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpHeadersHandlePendingHeadersRefreshRequestRequest;
use Revolution\Copilot\Types\Rpc\McpHeadersHandlePendingHeadersRefreshRequestResult;

/**
 * Pending MCP headers RPC operations for dynamic headers refresh.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingMcpHeaders
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Responds to a pending MCP dynamic headers refresh request.
     *
     * Hosts that subscribe to mcp.headers_refresh_required use this to provide
     * short-lived per-server headers or to indicate that no dynamic headers are available.
     */
    public function handlePendingHeadersRefreshRequest(McpHeadersHandlePendingHeadersRefreshRequestRequest|array $params): McpHeadersHandlePendingHeadersRefreshRequestResult
    {
        $paramsArray = ($params instanceof McpHeadersHandlePendingHeadersRefreshRequestRequest
            ? $params
            : McpHeadersHandlePendingHeadersRefreshRequestRequest::fromArray($params))->toArray();

        $paramsArray['sessionId'] = $this->sessionId;

        return McpHeadersHandlePendingHeadersRefreshRequestResult::fromArray(
            $this->client->request('session.mcp.headers.handlePendingHeadersRefreshRequest', $paramsArray),
        );
    }
}
