<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PermissionDecisionRequest;
use Revolution\Copilot\Types\Rpc\PermissionRequestResult;
use Revolution\Copilot\Types\Rpc\PermissionsResetSessionApprovalsResult;
use Revolution\Copilot\Types\Rpc\PermissionsSetApproveAllRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetApproveAllResult;

/**
 * Pending session-scoped permissions RPC operations.
 *
 * Used to respond to permission requests received as session events (protocol v3+).
 * For protocol v2, permission requests are handled automatically by the permission handler.
 */
class PendingPermissions
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Handle a pending permission request by approving or denying it.
     */
    public function handlePendingPermissionRequest(PermissionDecisionRequest|array $params): PermissionRequestResult
    {
        $paramsArray = ($params instanceof PermissionDecisionRequest ? $params : PermissionDecisionRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionRequestResult::fromArray(
            $this->client->request('session.permissions.handlePendingPermissionRequest', $paramsArray),
        );
    }

    /**
     * Set approve-all mode for this session's permissions.
     */
    public function setApproveAll(PermissionsSetApproveAllRequest|array $params): PermissionsSetApproveAllResult
    {
        $paramsArray = ($params instanceof PermissionsSetApproveAllRequest ? $params : PermissionsSetApproveAllRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionsSetApproveAllResult::fromArray(
            $this->client->request('session.permissions.setApproveAll', $paramsArray),
        );
    }

    /**
     * Reset all session-scoped permission approvals.
     */
    public function resetSessionApprovals(): PermissionsResetSessionApprovalsResult
    {
        return PermissionsResetSessionApprovalsResult::fromArray(
            $this->client->request('session.permissions.resetSessionApprovals', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
