<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ManagedSettingsReadResult;

/**
 * Server-level managed settings RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerManagedSettings
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Discover and validate device-managed settings without creating a session.
     */
    public function read(): ManagedSettingsReadResult
    {
        return ManagedSettingsReadResult::fromArray(
            $this->client->request('managedSettings.read', []),
        );
    }
}
