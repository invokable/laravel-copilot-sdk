<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpOauthCheckDcrSupportRequest;
use Revolution\Copilot\Types\Rpc\McpOauthCheckDcrSupportResult;
use Revolution\Copilot\Types\Rpc\McpOauthIsMicrosoftAuthorizationServerRequest;
use Revolution\Copilot\Types\Rpc\McpOauthIsMicrosoftAuthorizationServerResult;

/**
 * Server-scoped MCP OAuth discovery RPC operations.
 */
class PendingServerMcpOauth
{
    public function __construct(protected JsonRpcClient $client) {}

    /**
     * Check whether a remote MCP server's authorization server supports Dynamic Client Registration.
     */
    public function checkDcrSupport(McpOauthCheckDcrSupportRequest|array $params): McpOauthCheckDcrSupportResult
    {
        $paramsArray = ($params instanceof McpOauthCheckDcrSupportRequest
            ? $params
            : McpOauthCheckDcrSupportRequest::fromArray($params))->toArray();

        return McpOauthCheckDcrSupportResult::fromArray(
            $this->client->request('mcp.oauth.checkDcrSupport', $paramsArray),
        );
    }

    /**
     * Determine whether an authorization server URL belongs to Microsoft Entra ID.
     */
    public function isMicrosoftAuthorizationServer(
        McpOauthIsMicrosoftAuthorizationServerRequest|array $params,
    ): McpOauthIsMicrosoftAuthorizationServerResult {
        $paramsArray = ($params instanceof McpOauthIsMicrosoftAuthorizationServerRequest
            ? $params
            : McpOauthIsMicrosoftAuthorizationServerRequest::fromArray($params))->toArray();

        return McpOauthIsMicrosoftAuthorizationServerResult::fromArray(
            $this->client->request('mcp.oauth.isMicrosoftAuthorizationServer', $paramsArray),
        );
    }
}
