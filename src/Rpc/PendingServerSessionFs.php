<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderParams;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderResult;

/**
 * Pending SessionFs RPC operations (server-scoped).
 *
 * Manages session filesystem provider registration.
 */
class PendingServerSessionFs
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Register a session filesystem provider.
     */
    public function setProvider(SessionFsSetProviderParams|array $params): SessionFsSetProviderResult
    {
        $paramsArray = ($params instanceof SessionFsSetProviderParams ? $params : SessionFsSetProviderParams::fromArray($params))->toArray();

        return SessionFsSetProviderResult::fromArray(
            $this->client->request('sessionFs.setProvider', $paramsArray),
        );
    }
}
