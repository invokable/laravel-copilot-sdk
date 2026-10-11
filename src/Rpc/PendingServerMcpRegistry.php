<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpRegistryCancelRequest;
use Revolution\Copilot\Types\Rpc\McpRegistryCancelResult;
use Revolution\Copilot\Types\Rpc\McpRegistryRequestIdResult;
use Revolution\Copilot\Types\Rpc\McpRegistrySearchRequest;
use Revolution\Copilot\Types\Rpc\McpRegistrySearchResult;

/**
 * Internal MCP registry RPC operations.
 *
 * @experimental
 *
 * @internal Internal SDK API; not part of the public surface.
 */
class PendingServerMcpRegistry
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /** Allocate an ID for one cancellable registry search. */
    public function allocateRequestId(): McpRegistryRequestIdResult
    {
        return McpRegistryRequestIdResult::fromArray(
            $this->client->request('mcp.registry.allocateRequestId', []),
        );
    }

    /** Search the MCP registry using an allocated request ID. */
    public function search(McpRegistrySearchRequest|array $params): McpRegistrySearchResult
    {
        $paramsArray = ($params instanceof McpRegistrySearchRequest
            ? $params
            : McpRegistrySearchRequest::fromArray($params))->toArray();

        return McpRegistrySearchResult::fromArray(
            $this->client->request('mcp.registry.search', $paramsArray),
        );
    }

    /** Cancel a running registry search or release an unused request ID. */
    public function cancel(McpRegistryCancelRequest|array $params): McpRegistryCancelResult
    {
        $paramsArray = ($params instanceof McpRegistryCancelRequest
            ? $params
            : McpRegistryCancelRequest::fromArray($params))->toArray();

        return McpRegistryCancelResult::fromArray(
            $this->client->request('mcp.registry.cancel', $paramsArray),
        );
    }
}
