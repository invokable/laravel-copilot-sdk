<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AccountGetQuotaResult;

/**
 * Pending account RPC operations.
 */
class PendingAccount
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Get account quota.
     */
    public function getQuota(): AccountGetQuotaResult
    {
        return AccountGetQuotaResult::fromArray(
            $this->client->request('account.getQuota', []),
        );
    }
}
