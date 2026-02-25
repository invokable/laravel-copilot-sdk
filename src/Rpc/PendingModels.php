<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ModelsListResult;

/**
 * Pending models RPC operations.
 */
class PendingModels
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * List available models.
     */
    public function list(): ModelsListResult
    {
        return ModelsListResult::fromArray(
            $this->client->request('models.list', []),
        );
    }
}
