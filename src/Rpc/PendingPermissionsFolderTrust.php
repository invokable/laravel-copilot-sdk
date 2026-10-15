<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\FolderTrustAddParams;
use Revolution\Copilot\Types\Rpc\FolderTrustAddResult;
use Revolution\Copilot\Types\Rpc\FolderTrustCheckParams;
use Revolution\Copilot\Types\Rpc\FolderTrustCheckResult;

/**
 * Folder trust session permissions RPC operations.
 */
class PendingPermissionsFolderTrust
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Check whether a path is trusted.
     */
    public function isTrusted(FolderTrustCheckParams|array $params): FolderTrustCheckResult
    {
        $paramsArray = ($params instanceof FolderTrustCheckParams ? $params : FolderTrustCheckParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FolderTrustCheckResult::fromArray(
            $this->client->request('session.permissions.folderTrust.isTrusted', $paramsArray),
        );
    }

    /**
     * Add a trusted path.
     */
    public function addTrusted(FolderTrustAddParams|array $params): FolderTrustAddResult
    {
        $paramsArray = ($params instanceof FolderTrustAddParams ? $params : FolderTrustAddParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FolderTrustAddResult::fromArray(
            $this->client->request('session.permissions.folderTrust.addTrusted', $paramsArray),
        );
    }
}
