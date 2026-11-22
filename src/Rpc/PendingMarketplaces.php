<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\MarketplaceCheckUpdateResult;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceCheckUpdateRequest;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceSearchRequest;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceSearchResult;

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

    public function checkUpdate(PluginMarketplaceCheckUpdateRequest|array $params): MarketplaceCheckUpdateResult
    {
        $paramsArray = ($params instanceof PluginMarketplaceCheckUpdateRequest
            ? $params
            : PluginMarketplaceCheckUpdateRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return MarketplaceCheckUpdateResult::fromArray(
            $this->client->request('session.plugins.marketplaces.checkUpdate', $paramsArray),
        );
    }

    public function search(PluginMarketplaceSearchRequest|array $params): PluginMarketplaceSearchResult
    {
        $paramsArray = ($params instanceof PluginMarketplaceSearchRequest
            ? $params
            : PluginMarketplaceSearchRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PluginMarketplaceSearchResult::fromArray(
            $this->client->request('session.plugins.marketplaces.search', $paramsArray),
        );
    }

    protected function request(string $method, array $params = []): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request($method, $params);
    }
}
