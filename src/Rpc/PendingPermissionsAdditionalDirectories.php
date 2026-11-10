<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PermissionsAdditionalDirectoriesListResult;

/**
 * Additional model-context directories RPC operations.
 *
 * @experimental
 */
class PendingPermissionsAdditionalDirectories
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List the session's additional model-context directories.
     */
    public function list(): PermissionsAdditionalDirectoriesListResult
    {
        return PermissionsAdditionalDirectoriesListResult::fromArray(
            $this->client->request('session.permissions.additionalDirectories.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
