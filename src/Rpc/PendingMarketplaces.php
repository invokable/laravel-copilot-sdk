<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Plugin marketplace RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingMarketplaces
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function list(): array
    {
        return $this->request('session.plugins.marketplaces.list');
    }

    public function add(array $params): array
    {
        return $this->request('session.plugins.marketplaces.add', $params);
    }

    public function remove(array $params): array
    {
        return $this->request('session.plugins.marketplaces.remove', $params);
    }

    public function browse(array $params = []): array
    {
        return $this->request('session.plugins.marketplaces.browse', $params);
    }

    public function refresh(array $params = []): array
    {
        return $this->request('session.plugins.marketplaces.refresh', $params);
    }

    protected function request(string $method, array $params = []): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request($method, $params);
    }
}
