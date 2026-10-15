<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PermissionLocationAddToolApprovalParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationAddToolApprovalResult;
use Revolution\Copilot\Types\Rpc\PermissionLocationApplyParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationApplyResult;
use Revolution\Copilot\Types\Rpc\PermissionLocationResolveParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationResolveResult;

/**
 * Location-scoped session permissions RPC operations.
 */
class PendingPermissionsLocations
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Resolve the permissions location for a working directory.
     */
    public function resolve(PermissionLocationResolveParams|array $params): PermissionLocationResolveResult
    {
        $paramsArray = ($params instanceof PermissionLocationResolveParams ? $params : PermissionLocationResolveParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionLocationResolveResult::fromArray(
            $this->client->request('session.permissions.locations.resolve', $paramsArray),
        );
    }

    /**
     * Apply permissions stored for a working directory.
     */
    public function apply(PermissionLocationApplyParams|array $params): PermissionLocationApplyResult
    {
        $paramsArray = ($params instanceof PermissionLocationApplyParams ? $params : PermissionLocationApplyParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionLocationApplyResult::fromArray(
            $this->client->request('session.permissions.locations.apply', $paramsArray),
        );
    }

    /**
     * Persist a tool approval for a permissions location.
     */
    public function addToolApproval(PermissionLocationAddToolApprovalParams|array $params): PermissionLocationAddToolApprovalResult
    {
        $paramsArray = ($params instanceof PermissionLocationAddToolApprovalParams ? $params : PermissionLocationAddToolApprovalParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionLocationAddToolApprovalResult::fromArray(
            $this->client->request('session.permissions.locations.addToolApproval', $paramsArray),
        );
    }
}
