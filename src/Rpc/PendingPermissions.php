<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AllowAllPermissionSetResult;
use Revolution\Copilot\Types\Rpc\AllowAllPermissionState;
use Revolution\Copilot\Types\Rpc\PendingPermissionRequestList;
use Revolution\Copilot\Types\Rpc\PermissionDecisionRequest;
use Revolution\Copilot\Types\Rpc\PermissionPromptShownNotification;
use Revolution\Copilot\Types\Rpc\PermissionRequestResult;
use Revolution\Copilot\Types\Rpc\PermissionsConfigureParams;
use Revolution\Copilot\Types\Rpc\PermissionsConfigureResult;
use Revolution\Copilot\Types\Rpc\PermissionsModifyRulesParams;
use Revolution\Copilot\Types\Rpc\PermissionsModifyRulesResult;
use Revolution\Copilot\Types\Rpc\PermissionsNotifyPromptShownResult;
use Revolution\Copilot\Types\Rpc\PermissionsPendingRequestsRequest;
use Revolution\Copilot\Types\Rpc\PermissionsResetSessionApprovalsResult;
use Revolution\Copilot\Types\Rpc\PermissionsSetAllowAllRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetApproveAllRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetApproveAllResult;
use Revolution\Copilot\Types\Rpc\PermissionsSetRequiredRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetRequiredResult;

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

    /**
     * Path-related permissions RPC operations.
     */
    public function paths(): PendingPermissionsPaths
    {
        return new PendingPermissionsPaths($this->client, $this->sessionId);
    }

    /**
     * URL-related permissions RPC operations.
     */
    public function urls(): PendingPermissionsUrls
    {
        return new PendingPermissionsUrls($this->client, $this->sessionId);
    }

    /**
     * Configure session permission policy fields.
     */
    public function configure(PermissionsConfigureParams|array $params): PermissionsConfigureResult
    {
        $paramsArray = ($params instanceof PermissionsConfigureParams ? $params : PermissionsConfigureParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionsConfigureResult::fromArray(
            $this->client->request('session.permissions.configure', $paramsArray),
        );
    }

    /**
     * List currently pending permission requests.
     */
    public function pendingRequests(PermissionsPendingRequestsRequest|array $params = []): PendingPermissionRequestList
    {
        $paramsArray = ($params instanceof PermissionsPendingRequestsRequest ? $params : PermissionsPendingRequestsRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PendingPermissionRequestList::fromArray(
            $this->client->request('session.permissions.pendingRequests', $paramsArray),
        );
    }

    /**
     * Add/remove session- or location-scoped permission rules.
     */
    public function modifyRules(PermissionsModifyRulesParams|array $params): PermissionsModifyRulesResult
    {
        $paramsArray = ($params instanceof PermissionsModifyRulesParams ? $params : PermissionsModifyRulesParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionsModifyRulesResult::fromArray(
            $this->client->request('session.permissions.modifyRules', $paramsArray),
        );
    }

    /**
     * Toggle whether permission prompts are bridged as session events.
     */
    public function setRequired(PermissionsSetRequiredRequest|array $params): PermissionsSetRequiredResult
    {
        $paramsArray = ($params instanceof PermissionsSetRequiredRequest ? $params : PermissionsSetRequiredRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionsSetRequiredResult::fromArray(
            $this->client->request('session.permissions.setRequired', $paramsArray),
        );
    }

    /**
     * Notify that a permission prompt was shown to the user.
     */
    public function notifyPromptShown(PermissionPromptShownNotification|array $params): PermissionsNotifyPromptShownResult
    {
        $paramsArray = ($params instanceof PermissionPromptShownNotification ? $params : PermissionPromptShownNotification::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionsNotifyPromptShownResult::fromArray(
            $this->client->request('session.permissions.notifyPromptShown', $paramsArray),
        );
    }

    /**
     * Set the allow-all mode for this session's permissions.
     *
     * @experimental
     */
    public function setAllowAll(PermissionsSetAllowAllRequest|array $params): AllowAllPermissionSetResult
    {
        $paramsArray = ($params instanceof PermissionsSetAllowAllRequest ? $params : PermissionsSetAllowAllRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return AllowAllPermissionSetResult::fromArray(
            $this->client->request('session.permissions.setAllowAll', $paramsArray),
        );
    }

    /**
     * Get the current allow-all mode for this session's permissions.
     *
     * @experimental
     */
    public function getAllowAll(): AllowAllPermissionState
    {
        return AllowAllPermissionState::fromArray(
            $this->client->request('session.permissions.getAllowAll', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
