<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ModelList;
use Revolution\Copilot\Types\Rpc\ModelsListRequest;

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
     *                                                 resolves that token for per-user model listing.
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
}
