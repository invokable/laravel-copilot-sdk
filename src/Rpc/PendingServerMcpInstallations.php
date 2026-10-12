<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpInstallationOperationRequest;
use Revolution\Copilot\Types\Rpc\McpInstallationsRequest;

/**
 * MCP installation operation management RPC methods.
 *
 * @experimental
 *
 * @internal Internal SDK API; not part of the public surface.
 */
class PendingServerMcpInstallations
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Lists owned MCP installations.
     *
     * @return array<string, mixed>
     */
    public function list(McpInstallationsRequest|array $params): array
    {
        $paramsArray = ($params instanceof McpInstallationsRequest
            ? $params
            : McpInstallationsRequest::fromArray($params))->toArray();

        return $this->client->request('mcp.installations.list', $paramsArray);
    }

    /**
     * Recovers pending MCP installation operations.
     *
     * @return array<string, mixed>
     */
    public function recover(McpInstallationsRequest|array $params): array
    {
        $paramsArray = ($params instanceof McpInstallationsRequest
            ? $params
            : McpInstallationsRequest::fromArray($params))->toArray();

        return $this->client->request('mcp.installations.recover', $paramsArray);
    }

    /**
     * Gets the status of a known MCP installation operation.
     *
     * @return array<string, mixed>
     */
    public function status(McpInstallationOperationRequest|array $params): array
    {
        $paramsArray = ($params instanceof McpInstallationOperationRequest
            ? $params
            : McpInstallationOperationRequest::fromArray($params))->toArray();

        return $this->client->request('mcp.installations.status', $paramsArray);
    }

    /**
     * Cancels a known MCP installation operation.
     *
     * @return array<string, mixed>
     */
    public function cancel(McpInstallationOperationRequest|array $params): array
    {
        $paramsArray = ($params instanceof McpInstallationOperationRequest
            ? $params
            : McpInstallationOperationRequest::fromArray($params))->toArray();

        return $this->client->request('mcp.installations.cancel', $paramsArray);
    }
}
