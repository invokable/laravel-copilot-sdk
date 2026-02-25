<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PingParams;
use Revolution\Copilot\Types\Rpc\PingResult;

/**
 * Typed server-scoped RPC methods (no session required).
 *
 * Usage:
 * ```php
 * $client->rpc()->ping(new PingParams(message: 'hello'));
 * $client->rpc()->models()->list();
 * $client->rpc()->tools()->list();
 * $client->rpc()->account()->getQuota();
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
    public function ping(PingParams|array $params = []): PingResult
    {
        $paramsArray = $params instanceof PingParams ? $params->toArray() : $params;

        return PingResult::fromArray(
            $this->client->request('ping', $paramsArray),
        );
    }

    /**
     * Models RPC operations.
     */
    public function models(): PendingModels
    {
        return new PendingModels($this->client);
    }

    /**
     * Tools RPC operations.
     */
    public function tools(): PendingTools
    {
        return new PendingTools($this->client);
    }

    /**
     * Account RPC operations.
     */
    public function account(): PendingAccount
    {
        return new PendingAccount($this->client);
    }
}
