<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Pending server-level user settings RPC operations.
 */
class PendingServerUserSettings
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Drops this runtime process's in-memory user settings cache so the next settings read observes disk.
     */
    public function reload(): void
    {
        $this->client->request('user.settings.reload', []);
    }
}
