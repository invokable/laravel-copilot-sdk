<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PermissionsUrlsSetUnrestrictedModeResult;
use Revolution\Copilot\Types\Rpc\PermissionUrlsSetUnrestrictedModeParams;

/**
 * URL-related session permissions RPC operations.
 */
class PendingPermissionsUrls
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Toggle unrestricted URL mode.
     */
    public function setUnrestrictedMode(PermissionUrlsSetUnrestrictedModeParams|array $params): PermissionsUrlsSetUnrestrictedModeResult
    {
        $paramsArray = ($params instanceof PermissionUrlsSetUnrestrictedModeParams ? $params : PermissionUrlsSetUnrestrictedModeParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return PermissionsUrlsSetUnrestrictedModeResult::fromArray(
            $this->client->request('session.permissions.urls.setUnrestrictedMode', $paramsArray),
        );
    }
}
