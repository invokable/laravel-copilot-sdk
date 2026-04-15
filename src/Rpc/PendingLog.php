<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\LogRequest;
use Revolution\Copilot\Types\Rpc\LogResult;

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
    public function log(LogRequest|array $params): LogResult
    {
        $paramsArray = ($params instanceof LogRequest ? $params : LogRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return LogResult::fromArray(
            $this->client->request('session.log', $paramsArray),
        );
    }
}
