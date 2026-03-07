<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionPermissionsHandlePendingPermissionRequestParams;
use Revolution\Copilot\Types\Rpc\SessionPermissionsHandlePendingPermissionRequestResult;

/**
 * Pending session-scoped permissions RPC operations.
 *
 * Used to respond to permission requests received as session events (protocol v3+).
 * For protocol v2, permission requests are handled automatically by the permission handler.
 */
class PendingSessionPermissions
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Handle a pending permission request by approving or denying it.
     */
    public function handlePendingPermissionRequest(SessionPermissionsHandlePendingPermissionRequestParams|array $params): SessionPermissionsHandlePendingPermissionRequestResult
    {
        $paramsArray = $params instanceof SessionPermissionsHandlePendingPermissionRequestParams ? $params->toArray() : $params;
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionPermissionsHandlePendingPermissionRequestResult::fromArray(
            $this->client->request('session.permissions.handlePendingPermissionRequest', $paramsArray),
        );
    }
}
