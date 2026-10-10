<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\QuotaWarningProjection;
use Revolution\Copilot\Types\Rpc\SessionQuotaGetResult;
use Revolution\Copilot\Types\Rpc\SessionQuotaRefreshResult;

/**
 * Pending quota RPC operations for a session.
 *
 * @experimental This API group may change or be removed.
 */
class PendingQuota
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /** Read the current quota projection without a network refresh. */
    public function get(): SessionQuotaGetResult
    {
        return SessionQuotaGetResult::fromArray(
            $this->client->request('session.quota.get', ['sessionId' => $this->sessionId]),
        );
    }

    /** Refresh provider quota data and return the resulting projection. */
    public function refresh(): SessionQuotaRefreshResult
    {
        return SessionQuotaRefreshResult::fromArray(
            $this->client->request('session.quota.refresh', ['sessionId' => $this->sessionId]),
        );
    }

    /**
     * Return and clear pending quota warnings.
     *
     * @return array<QuotaWarningProjection>
     */
    public function takeWarnings(): array
    {
        return array_map(
            static fn (array $warning) => QuotaWarningProjection::fromArray($warning),
            $this->client->request('session.quota.takeWarnings', ['sessionId' => $this->sessionId]),
        );
    }
}
