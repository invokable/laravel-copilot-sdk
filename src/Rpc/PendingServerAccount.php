<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AccountGetQuotaRequest;
use Revolution\Copilot\Types\Rpc\AccountGetQuotaResult;

/**
 * Pending account RPC operations.
 */
class PendingServerAccount
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Get account quota.
     *
     * @param  AccountGetQuotaRequest|array|null  $params  Optional params. When gitHubToken is provided,
     *                                                     resolves that token for per-user quota instead of global auth.
     */
    public function getQuota(AccountGetQuotaRequest|array|null $params = null): AccountGetQuotaResult
    {
        $paramsArray = $params === null
            ? []
            : ($params instanceof AccountGetQuotaRequest ? $params : AccountGetQuotaRequest::fromArray($params))->toArray();

        return AccountGetQuotaResult::fromArray(
            $this->client->request('account.getQuota', $paramsArray),
        );
    }
}
