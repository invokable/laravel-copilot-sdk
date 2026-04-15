<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ModelList;

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
     */
    public function list(): ModelList
    {
        return ModelList::fromArray(
            $this->client->request('models.list', []),
        );
    }
}
