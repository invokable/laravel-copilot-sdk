<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\BuiltInModelCatalog;
use Revolution\Copilot\Types\Rpc\ModelList;
use Revolution\Copilot\Types\Rpc\ModelsListRequest;
use Revolution\Copilot\Types\Rpc\ModelsListTokenPriceUnitsRequest;
use Revolution\Copilot\Types\Rpc\ModelTokenPriceUnitList;

/**
 * Pending models RPC operations.
 */
class PendingServerModels
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * List available models.
     *
     * @param  ModelsListRequest|array|null  $params  Optional params. When gitHubToken is provided,
     *                                                resolves that token for per-user model listing.
     */
    public function list(ModelsListRequest|array|null $params = null): ModelList
    {
        $paramsArray = $params === null
            ? []
            : ($params instanceof ModelsListRequest ? $params : ModelsListRequest::fromArray($params))->toArray();

        return ModelList::fromArray(
            $this->client->request('models.list', $paramsArray),
        );
    }

    /**
     * List the billing units used by model token prices.
     *
     * @param  ModelsListTokenPriceUnitsRequest|array|null  $params  Optional account selection parameters.
     */
    public function listTokenPriceUnits(ModelsListTokenPriceUnitsRequest|array|null $params = null): ModelTokenPriceUnitList
    {
        $paramsArray = $params === null
            ? []
            : ($params instanceof ModelsListTokenPriceUnitsRequest ? $params : ModelsListTokenPriceUnitsRequest::fromArray($params))->toArray();

        return ModelTokenPriceUnitList::fromArray(
            $this->client->request('models.listTokenPriceUnits', $paramsArray),
        );
    }

    /**
     * Retrieve the built-in model catalog.
     */
    public function getBuiltInCatalog(): BuiltInModelCatalog
    {
        return BuiltInModelCatalog::fromArray(
            $this->client->request('models.getBuiltInCatalog', []),
        );
    }
}
