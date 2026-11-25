<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsGrantParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsGrantResult;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsListParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsListResult;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsRevokeParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsRevokeResult;

/**
 * Pending server-level plugin extension approval RPC operations.
 *
 * @experimental
 */
class PendingServerPermissionsExtensionApprovals
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Persist an eligible pending request; the host must still answer it normally.
     *
     * @experimental
     */
    public function grant(PermissionsExtensionApprovalsGrantParams|array $params): PermissionsExtensionApprovalsGrantResult
    {
        $paramsArray = ($params instanceof PermissionsExtensionApprovalsGrantParams
            ? $params
            : PermissionsExtensionApprovalsGrantParams::fromArray($params))->toArray();

        return PermissionsExtensionApprovalsGrantResult::fromArray(
            $this->client->request('permissions.extensionApprovals.grant', $paramsArray),
        );
    }

    /**
     * List user-scoped plugin extension approvals.
     *
     * @experimental
     */
    public function list(PermissionsExtensionApprovalsListParams|array $params = []): PermissionsExtensionApprovalsListResult
    {
        $paramsArray = ($params instanceof PermissionsExtensionApprovalsListParams
            ? $params
            : PermissionsExtensionApprovalsListParams::fromArray($params))->toArray();

        return PermissionsExtensionApprovalsListResult::fromArray(
            $this->client->request('permissions.extensionApprovals.list', $paramsArray),
        );
    }

    /**
     * Revoke a user-scoped plugin extension installation approval.
     *
     * @experimental
     */
    public function revoke(PermissionsExtensionApprovalsRevokeParams|array $params): PermissionsExtensionApprovalsRevokeResult
    {
        $paramsArray = ($params instanceof PermissionsExtensionApprovalsRevokeParams
            ? $params
            : PermissionsExtensionApprovalsRevokeParams::fromArray($params))->toArray();

        return PermissionsExtensionApprovalsRevokeResult::fromArray(
            $this->client->request('permissions.extensionApprovals.revoke', $paramsArray),
        );
    }
}
