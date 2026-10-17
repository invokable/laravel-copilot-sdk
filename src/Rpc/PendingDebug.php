<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsRequest;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsResult;

/**
 * Pending session-scoped debug RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingDebug
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Collects session logs and additional entries into an archive or directory.
     *
     * @experimental
     */
    public function collectLogs(DebugCollectLogsRequest|array $params): DebugCollectLogsResult
    {
        $paramsArray = ($params instanceof DebugCollectLogsRequest ? $params : DebugCollectLogsRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return DebugCollectLogsResult::fromArray(
            $this->client->request('session.debug.collectLogs', $paramsArray),
        );
    }
}
