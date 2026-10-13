<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpOauthAuthenticationStateChangedRequest;
use Revolution\Copilot\Types\Rpc\McpOauthCompleteRequest;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingRequest;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingResult;
use Revolution\Copilot\Types\Rpc\McpOauthLoginRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginResult;
use Revolution\Copilot\Types\Rpc\McpOauthProbeRequest;
use Revolution\Copilot\Types\Rpc\McpOauthProbeResult;
use Revolution\Copilot\Types\Rpc\McpOauthRespondRequest;
use Revolution\Copilot\Types\Rpc\McpOauthRespondResult;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthCancelLoginRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthCancelLoginResult;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthPrepareLoginRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthPrepareLoginResult;

/**
 * Pending MCP OAuth RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingMcpOauth
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Resolves a pending MCP OAuth request with a host-provided token or cancellation.
     */
    public function handlePendingRequest(McpOauthHandlePendingRequest|array $params): McpOauthHandlePendingResult
    {
        $paramsArray = ($params instanceof McpOauthHandlePendingRequest ? $params : McpOauthHandlePendingRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpOauthHandlePendingResult::fromArray(
            $this->client->request('session.mcp.oauth.handlePendingRequest', $paramsArray),
        );
    }

    /**
     * Notify the session that persisted MCP OAuth credentials changed.
     */
    public function authenticationStateChanged(McpOauthAuthenticationStateChangedRequest|array $params = []): void
    {
        $paramsArray = ($params instanceof McpOauthAuthenticationStateChangedRequest
            ? $params
            : McpOauthAuthenticationStateChangedRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.mcp.oauth.authenticationStateChanged', $paramsArray);
    }

    /**
     * Initiate MCP OAuth login for a remote server.
     */
    public function login(McpOauthLoginRequest|array $params): McpOauthLoginResult
    {
        $paramsArray = ($params instanceof McpOauthLoginRequest ? $params : McpOauthLoginRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpOauthLoginResult::fromArray(
            $this->client->request('session.mcp.oauth.login', $paramsArray),
        );
    }

    /**
     * Resolves a pending MCP OAuth request emitted as mcp.oauth_required.
     */
    public function respond(McpOauthRespondRequest|array $params): McpOauthRespondResult
    {
        $paramsArray = ($params instanceof McpOauthRespondRequest ? $params : McpOauthRespondRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpOauthRespondResult::fromArray(
            $this->client->request('session.mcp.oauth.respond', $paramsArray),
        );
    }

    /**
     * Complete an MCP OAuth login through a host-managed redirect callback.
     */
    public function complete(McpOauthCompleteRequest|array $params): void
    {
        $paramsArray = ($params instanceof McpOauthCompleteRequest ? $params : McpOauthCompleteRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.mcp.oauth.complete', $paramsArray);
    }

    /**
     * Passively probe OAuth for a configured remote MCP server.
     */
    public function probe(McpOauthProbeRequest|array $params): McpOauthProbeResult
    {
        $paramsArray = ($params instanceof McpOauthProbeRequest ? $params : McpOauthProbeRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpOauthProbeResult::fromArray(
            $this->client->request('session.mcp.oauth.probe', $paramsArray),
        );
    }

    /**
     * Prepare an owned MCP OAuth login without starting the login flow.
     */
    public function prepareLogin(SessionMcpOauthPrepareLoginRequest|array $params): SessionMcpOauthPrepareLoginResult
    {
        $paramsArray = ($params instanceof SessionMcpOauthPrepareLoginRequest
            ? $params
            : SessionMcpOauthPrepareLoginRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionMcpOauthPrepareLoginResult::fromArray(
            $this->client->request('session.mcp.oauth.prepareLogin', $paramsArray),
        );
    }

    /**
     * Cancel an owned MCP OAuth login.
     */
    public function cancelLogin(SessionMcpOauthCancelLoginRequest|array $params): SessionMcpOauthCancelLoginResult
    {
        $paramsArray = ($params instanceof SessionMcpOauthCancelLoginRequest
            ? $params
            : SessionMcpOauthCancelLoginRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionMcpOauthCancelLoginResult::fromArray(
            $this->client->request('session.mcp.oauth.cancelLogin', $paramsArray),
        );
    }
}
