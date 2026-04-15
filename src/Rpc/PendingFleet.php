<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\FleetStartRequest;
use Revolution\Copilot\Types\Rpc\FleetStartResult;

/**
 * Pending fleet RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingFleet
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Start fleet mode.
     */
    public function start(FleetStartRequest|array $params = []): FleetStartResult
    {
        $paramsArray = ($params instanceof FleetStartRequest ? $params : FleetStartRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FleetStartResult::fromArray(
            $this->client->request('session.fleet.start', $paramsArray),
        );
    }
}
