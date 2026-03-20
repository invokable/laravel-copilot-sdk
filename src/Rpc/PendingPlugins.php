<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionPluginsListResult;

/**
 * Pending plugins RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingPlugins
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List installed plugins.
     */
    public function list(): SessionPluginsListResult
    {
        return SessionPluginsListResult::fromArray(
            $this->client->request('session.plugins.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
