<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Pending large tool output RPC operations.
 *
 * @experimental This API group may change or be removed.
 */
class PendingServerLargeOutput
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Remove expired tool output files using the runtime retention policy.
     *
     * @experimental
     */
    public function cleanup(): void
    {
        $this->client->request('tools.largeOutput.cleanup', []);
    }
}
