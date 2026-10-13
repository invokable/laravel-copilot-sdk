<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Pending runtime lifecycle RPC operations.
 *
 * @experimental This API group may change or be removed.
 */
class PendingServerRuntime
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Gracefully shut down the runtime after it has completed cleanup.
     */
    public function shutdown(): void
    {
        $this->client->request('runtime.shutdown', [], 10.0);
    }
}
