<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/** Server-side connector account and catalog discovery RPCs. */
class PendingServerConnectors
{
    public function __construct(protected JsonRpcClient $client) {}

    public function getCapabilities(): array
    {
        return $this->client->request('connectors.getCapabilities', []);
    }

    public function getAccounts(): array
    {
        return $this->client->request('connectors.getAccounts', []);
    }

    public function list(string $accountId): array
    {
        return $this->client->request('connectors.list', ['accountId' => $accountId]);
    }

    public function refresh(string $accountId): array
    {
        return $this->client->request('connectors.refresh', ['accountId' => $accountId]);
    }
}
