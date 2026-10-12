<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\MarketplaceAddResult;
use Revolution\Copilot\Types\Rpc\MarketplaceBrowseResult;
use Revolution\Copilot\Types\Rpc\MarketplaceListResult;
use Revolution\Copilot\Types\Rpc\MarketplaceRefreshResult;
use Revolution\Copilot\Types\Rpc\MarketplaceRemoveResult;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesAddRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesBrowseRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesRefreshRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesRemoveRequest;

/**
 * Pending server-level plugin marketplace RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerPluginsMarketplaces
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * List registered marketplaces.
     */
    public function list(): MarketplaceListResult
    {
        return MarketplaceListResult::fromArray(
            $this->client->request('plugins.marketplaces.list', []),
        );
    }

    /**
     * Register a marketplace.
     */
    public function add(PluginsMarketplacesAddRequest|array $params): MarketplaceAddResult
    {
        $paramsArray = ($params instanceof PluginsMarketplacesAddRequest
            ? $params
            : PluginsMarketplacesAddRequest::fromArray($params))->toArray();

        return MarketplaceAddResult::fromArray(
            $this->client->request('plugins.marketplaces.add', $paramsArray),
        );
    }

    /**
     * Remove a registered marketplace.
     */
    public function remove(PluginsMarketplacesRemoveRequest|array $params): MarketplaceRemoveResult
    {
        $paramsArray = ($params instanceof PluginsMarketplacesRemoveRequest
            ? $params
            : PluginsMarketplacesRemoveRequest::fromArray($params))->toArray();

        return MarketplaceRemoveResult::fromArray(
            $this->client->request('plugins.marketplaces.remove', $paramsArray),
        );
    }

    /**
     * List plugins advertised by a marketplace.
     */
    public function browse(PluginsMarketplacesBrowseRequest|array $params): MarketplaceBrowseResult
    {
        $paramsArray = ($params instanceof PluginsMarketplacesBrowseRequest
            ? $params
            : PluginsMarketplacesBrowseRequest::fromArray($params))->toArray();

        return MarketplaceBrowseResult::fromArray(
            $this->client->request('plugins.marketplaces.browse', $paramsArray),
        );
    }

    /**
     * Refresh one or all registered marketplace catalogs.
     */
    public function refresh(PluginsMarketplacesRefreshRequest|array $params = []): MarketplaceRefreshResult
    {
        $paramsArray = ($params instanceof PluginsMarketplacesRefreshRequest
            ? $params
            : PluginsMarketplacesRefreshRequest::fromArray($params))->toArray();

        return MarketplaceRefreshResult::fromArray(
            $this->client->request('plugins.marketplaces.refresh', $paramsArray),
        );
    }
}
