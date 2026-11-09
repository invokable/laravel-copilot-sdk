<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\NetworkSetProviderRequest;

/**
 * Pending server-level network callback RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerNetwork
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Register this SDK connection's network callbacks.
     *
     * @experimental
     */
    public function setProvider(NetworkSetProviderRequest|array $params): void
    {
        $paramsArray = ($params instanceof NetworkSetProviderRequest
            ? $params
            : NetworkSetProviderRequest::fromArray($params))->toArray();

        $this->client->request('network.setProvider', $paramsArray);
    }
}
