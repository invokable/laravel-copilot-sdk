<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\UsageGetMetricsResult;
use Revolution\Copilot\Types\Rpc\UsageSetCodeChangesRequest;

/**
 * Pending usage RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingUsage
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get session usage metrics.
     */
    public function getMetrics(): UsageGetMetricsResult
    {
        return UsageGetMetricsResult::fromArray(
            $this->client->request('session.usage.getMetrics', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Replace the absolute code-change totals reported by a relay host.
     *
     * @experimental
     */
    public function setCodeChanges(UsageSetCodeChangesRequest|array $params): void
    {
        $paramsArray = ($params instanceof UsageSetCodeChangesRequest
            ? $params
            : UsageSetCodeChangesRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.usage.setCodeChanges', $paramsArray);
    }
}
