<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\RemoteEnableResult;

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
     * Enable remote session support (Mission Control integration).
     *
     * When enabled, sessions in a GitHub repository working directory are
     * accessible from GitHub web and mobile.
     */
    public function enable(): RemoteEnableResult
    {
        return RemoteEnableResult::fromArray(
            $this->client->request('session.remote.enable', [
                'sessionId' => $this->sessionId,
            ]),
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
}
