<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionLogParams;
use Revolution\Copilot\Types\Rpc\SessionLogResult;

/**
 * Pending log RPC operations for a session.
 */
class PendingLog
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Log a message to the session timeline.
     */
    public function log(SessionLogParams|array $params): SessionLogResult
    {
        $paramsArray = ($params instanceof SessionLogParams ? $params : SessionLogParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionLogResult::fromArray(
            $this->client->request('session.log', $paramsArray),
        );
    }
}
