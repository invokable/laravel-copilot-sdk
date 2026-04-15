<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PingRequest;
use Revolution\Copilot\Types\Rpc\PingResult;

/**
 * Typed server-scoped RPC methods (no session required).
 *
 * Usage:
 * ```php
 * $client->rpc()->ping(new PingRequest(message: 'hello'));
 * $client->rpc()->models()->list();
 * $client->rpc()->tools()->list();
 * $client->rpc()->account()->getQuota();
 * $client->rpc()->mcp()->config()->list();
 * $client->rpc()->sessionFs()->setProvider(...);
 * $client->rpc()->sessions()->fork(...);
 * ```
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
}
