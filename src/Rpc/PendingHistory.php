<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\HistoryCompactResult;
use Revolution\Copilot\Types\Rpc\HistoryTruncateRequest;
use Revolution\Copilot\Types\Rpc\HistoryTruncateResult;

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
    public function compact(): HistoryCompactResult
    {
        return HistoryCompactResult::fromArray(
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
    public function truncate(HistoryTruncateRequest|array $params): HistoryTruncateResult
    {
        $paramsArray = ($params instanceof HistoryTruncateRequest ? $params : HistoryTruncateRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return HistoryTruncateResult::fromArray(
            $this->client->request('session.history.truncate', $paramsArray),
        );
    }
}
