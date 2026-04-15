<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\UsageGetMetricsResult;

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
}
