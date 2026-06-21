<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ConnectRequest;
use Revolution\Copilot\Types\Rpc\ConnectResult;
use Revolution\Copilot\Types\Rpc\PingRequest;
use Revolution\Copilot\Types\Rpc\PingResult;

/**
 * Typed server-scoped RPC methods (no session required).
 */
class ServerRpc
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Send a ping request.
     */
    public function ping(PingRequest|array $params = []): PingResult
    {
        $paramsArray = $params instanceof PingRequest ? $params->toArray() : $params;

        return PingResult::fromArray(
            $this->client->request('ping', $paramsArray),
        );
    }

    /**
     * Send a connect handshake request.
     *
     * @internal Part of the SDK's internal surface.
     */
    public function connect(ConnectRequest|array $params = []): ConnectResult
    {
        $paramsArray = $params instanceof ConnectRequest ? $params->toArray() : $params;

        return ConnectResult::fromArray(
            $this->client->request('connect', $paramsArray),
        );
    }

    /**
     * Models RPC operations.
     */
    public function models(): PendingServerModels
    {
        return new PendingServerModels($this->client);
    }

    /**
     * Tools RPC operations.
     */
    public function tools(): PendingServerTools
    {
        return new PendingServerTools($this->client);
    }

    /**
     * Account RPC operations.
     */
    public function account(): PendingServerAccount
    {
        return new PendingServerAccount($this->client);
    }

    /**
     * MCP configuration RPC operations.
     */
    public function mcp(): PendingServerMcpConfig
    {
        return new PendingServerMcpConfig($this->client);
    }

    /**
     * SessionFs RPC operations.
     */
    public function sessionFs(): PendingServerSessionFs
    {
        return new PendingServerSessionFs($this->client);
    }

    /**
     * Sessions RPC operations (fork).
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function sessions(): PendingSessions
    {
        return new PendingSessions($this->client);
    }

    /**
     * Server-level skills RPC operations.
     */
    public function skills(): PendingServerSkills
    {
        return new PendingServerSkills($this->client);
    }

    /**
     * Server-level agents RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function agents(): PendingServerAgents
    {
        return new PendingServerAgents($this->client);
    }

    /**
     * Server-level instructions RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function instructions(): PendingServerInstructions
    {
        return new PendingServerInstructions($this->client);
    }

    /**
     * Server-level user settings RPC operations.
     */
    public function userSettings(): PendingServerUserSettings
    {
        return new PendingServerUserSettings($this->client);
    }

    /**
     * LLM inference callback provider RPC operations.
     *
     * Allows this client to register as the LLM inference provider and deliver
     * HTTP response frames for in-flight model-layer requests.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function llmInference(): PendingServerLlmInference
    {
        return new PendingServerLlmInference($this->client);
    }
}
