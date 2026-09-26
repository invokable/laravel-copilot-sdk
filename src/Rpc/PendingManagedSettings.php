<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Session-managed settings RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingManagedSettings
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function get(): array
    {
        return $this->client->request('session.managedSettings.get', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
