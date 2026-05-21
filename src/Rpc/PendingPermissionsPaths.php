<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PermissionPathsAddParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsAllowedCheckParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsAllowedCheckResult;
use Revolution\Copilot\Types\Rpc\PermissionPathsList;
use Revolution\Copilot\Types\Rpc\PermissionPathsUpdatePrimaryParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsWorkspaceCheckParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsWorkspaceCheckResult;
use Revolution\Copilot\Types\Rpc\PermissionsPathsAddResult;
use Revolution\Copilot\Types\Rpc\PermissionsPathsListRequest;
use Revolution\Copilot\Types\Rpc\PermissionsPathsUpdatePrimaryResult;

/**
 * Path-related session permissions RPC operations.
 */
class PendingPermissionsPaths
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List currently allowed paths and the primary path.
     */
    public function list(PermissionsPathsListRequest|array $params = []): PermissionPathsList
    {
        $paramsArray = ($params instanceof PermissionsPathsListRequest ? $params : PermissionsPathsListRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionPathsList::fromArray(
            $this->client->request('session.permissions.paths.list', $paramsArray),
        );
    }

    /**
     * Add a path to the allowed directories list.
     */
    public function add(PermissionPathsAddParams|array $params): PermissionsPathsAddResult
    {
        $paramsArray = ($params instanceof PermissionPathsAddParams ? $params : PermissionPathsAddParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionsPathsAddResult::fromArray(
            $this->client->request('session.permissions.paths.add', $paramsArray),
        );
    }

    /**
     * Update the session primary path.
     */
    public function updatePrimary(PermissionPathsUpdatePrimaryParams|array $params): PermissionsPathsUpdatePrimaryResult
    {
        $paramsArray = ($params instanceof PermissionPathsUpdatePrimaryParams ? $params : PermissionPathsUpdatePrimaryParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionsPathsUpdatePrimaryResult::fromArray(
            $this->client->request('session.permissions.paths.updatePrimary', $paramsArray),
        );
    }

    /**
     * Check whether a path is within currently allowed directories.
     */
    public function isPathWithinAllowedDirectories(PermissionPathsAllowedCheckParams|array $params): PermissionPathsAllowedCheckResult
    {
        $paramsArray = ($params instanceof PermissionPathsAllowedCheckParams ? $params : PermissionPathsAllowedCheckParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionPathsAllowedCheckResult::fromArray(
            $this->client->request('session.permissions.paths.isPathWithinAllowedDirectories', $paramsArray),
        );
    }

    /**
     * Check whether a path is within the workspace directory.
     */
    public function isPathWithinWorkspace(PermissionPathsWorkspaceCheckParams|array $params): PermissionPathsWorkspaceCheckResult
    {
        $paramsArray = ($params instanceof PermissionPathsWorkspaceCheckParams ? $params : PermissionPathsWorkspaceCheckParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionPathsWorkspaceCheckResult::fromArray(
            $this->client->request('session.permissions.paths.isPathWithinWorkspace', $paramsArray),
        );
    }
}
