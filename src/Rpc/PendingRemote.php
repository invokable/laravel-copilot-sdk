<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ConnectRemoteSessionParams;
use Revolution\Copilot\Types\Rpc\RemoteEnableRequest;
use Revolution\Copilot\Types\Rpc\RemoteEnableResult;
use Revolution\Copilot\Types\Rpc\RemoteSessionConnectionResult;

/**
 * Pending remote RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingRemote
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Enable remote session support.
     *
     * When enabled, sessions in a GitHub repository working directory are
     * accessible from GitHub web and mobile.
     *
     * @param  RemoteEnableRequest|array|null  $params  Optional remote session mode ("off", "export", or "on").
     */
    public function enable(RemoteEnableRequest|array|null $params = null): RemoteEnableResult
    {
        $paramsArray = [];

        if ($params !== null) {
            $paramsArray = ($params instanceof RemoteEnableRequest ? $params : RemoteEnableRequest::fromArray($params))->toArray();
        }

        $paramsArray['sessionId'] = $this->sessionId;

        return RemoteEnableResult::fromArray(
            $this->client->request('session.remote.enable', $paramsArray),
        );
    }

    /**
     * Disable remote session support.
     */
    public function disable(): void
    {
        $this->client->request('session.remote.disable', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Connect to a remote session.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function connectRemoteSession(ConnectRemoteSessionParams|array $params): RemoteSessionConnectionResult
    {
        $paramsArray = ($params instanceof ConnectRemoteSessionParams ? $params : ConnectRemoteSessionParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return RemoteSessionConnectionResult::fromArray(
            $this->client->request('session.remote.connectRemoteSession', $paramsArray),
        );
    }
}
