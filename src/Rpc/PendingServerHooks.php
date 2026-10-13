<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\HooksDiscoverRequest;
use Revolution\Copilot\Types\Rpc\HooksDiscoverResult;

/**
 * Pending server-scoped hooks RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerHooks
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Discover hooks without creating a session.
     *
     * @experimental This API is experimental and may change or be removed.
     */
    public function discover(HooksDiscoverRequest|array $params = []): HooksDiscoverResult
    {
        $paramsArray = ($params instanceof HooksDiscoverRequest ? $params : HooksDiscoverRequest::fromArray($params))->toArray();

        return HooksDiscoverResult::fromArray(
            $this->client->request('hooks.discover', $paramsArray),
        );
    }
}
