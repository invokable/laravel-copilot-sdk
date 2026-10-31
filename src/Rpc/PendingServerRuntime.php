<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\RuntimeOpenBrowserRequest;

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

    /**
     * Open an authorization URL using the runtime's browser policy.
     *
     * @experimental
     */
    public function openBrowser(RuntimeOpenBrowserRequest|array $params): void
    {
        $paramsArray = ($params instanceof RuntimeOpenBrowserRequest
            ? $params
            : RuntimeOpenBrowserRequest::fromArray($params))->toArray();

        $this->client->request('runtime.openBrowser', $paramsArray);
    }
}
