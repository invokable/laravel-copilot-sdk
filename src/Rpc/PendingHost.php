<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostListSessionsResult;

/**
 * Pending host-level RPC operations.
 *
 * @experimental This API group may change or be removed.
 */
class PendingHost
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Read the advertised live and dormant session catalog without subscribing.
     */
    public function listSessions(HostListSessionsRequest|array $params = []): HostListSessionsResult
    {
        $paramsArray = $params instanceof HostListSessionsRequest
            ? $params->toArray()
            : HostListSessionsRequest::fromArray($params)->toArray();

        return HostListSessionsResult::fromArray(
            $this->client->request('host.listSessions', $paramsArray),
        );
    }
}
