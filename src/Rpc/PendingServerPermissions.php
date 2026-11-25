<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Pending server-level permissions RPC operations.
 *
 * @experimental
 */
class PendingServerPermissions
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Manage persisted plugin extension approvals.
     *
     * @experimental
     */
    public function extensionApprovals(): PendingServerPermissionsExtensionApprovals
    {
        return new PendingServerPermissionsExtensionApprovals($this->client);
    }
}
