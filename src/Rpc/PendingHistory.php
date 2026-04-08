<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionHistoryCompactResult;
use Revolution\Copilot\Types\Rpc\SessionHistoryTruncateParams;
use Revolution\Copilot\Types\Rpc\SessionHistoryTruncateResult;

/**
 * Pending history RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingHistory
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Compact the session history.
     */
    public function compact(): SessionHistoryCompactResult
    {
        return SessionHistoryCompactResult::fromArray(
            $this->client->request('session.history.compact', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Truncate session history to a specific event.
     *
     * This event and all events after it are removed from the session.
     */
    public function truncate(SessionHistoryTruncateParams|array $params): SessionHistoryTruncateResult
    {
        $paramsArray = ($params instanceof SessionHistoryTruncateParams ? $params : SessionHistoryTruncateParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionHistoryTruncateResult::fromArray(
            $this->client->request('session.history.truncate', $paramsArray),
        );
    }
}
