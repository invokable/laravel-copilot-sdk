<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpSecretsDeleteParams;
use Revolution\Copilot\Types\Rpc\McpSecretsDeleteServerParams;
use Revolution\Copilot\Types\Rpc\McpSecretsSaveParams;
use Revolution\Copilot\Types\Rpc\McpSecretsSaveResult;

class PendingServerMcpSecrets
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Persist a legacy MCP configuration credential.
     *
     * @experimental
     */
    public function save(McpSecretsSaveParams|array $params): McpSecretsSaveResult
    {
        $paramsArray = ($params instanceof McpSecretsSaveParams ? $params : McpSecretsSaveParams::fromArray($params))->toArray();

        return McpSecretsSaveResult::fromArray(
            $this->client->request('mcp.secrets.save', $paramsArray),
        );
    }

    /**
     * Delete a legacy MCP configuration credential.
     *
     * @experimental
     */
    public function delete(McpSecretsDeleteParams|array $params): void
    {
        $paramsArray = ($params instanceof McpSecretsDeleteParams ? $params : McpSecretsDeleteParams::fromArray($params))->toArray();

        $this->client->request('mcp.secrets.delete', $paramsArray);
    }

    /**
     * Delete legacy credentials indexed for an MCP server.
     *
     * @experimental
     */
    public function deleteServer(McpSecretsDeleteServerParams|array $params): void
    {
        $paramsArray = ($params instanceof McpSecretsDeleteServerParams ? $params : McpSecretsDeleteServerParams::fromArray($params))->toArray();

        $this->client->request('mcp.secrets.deleteServer', $paramsArray);
    }
}
