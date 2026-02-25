<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionFleetStartParams;
use Revolution\Copilot\Types\Rpc\SessionFleetStartResult;

/**
 * Pending fleet RPC operations for a session.
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
    public function start(SessionFleetStartParams|array $params = []): SessionFleetStartResult
    {
        $paramsArray = $params instanceof SessionFleetStartParams ? $params->toArray() : $params;
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionFleetStartResult::fromArray(
            $this->client->request('session.fleet.start', $paramsArray),
        );
    }
}
