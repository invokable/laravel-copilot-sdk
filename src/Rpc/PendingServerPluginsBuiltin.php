<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PluginsBuiltinSetRequest;

/**
 * Pending server-level built-in plugin RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerPluginsBuiltin
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Replace the trusted built-in plugin directories.
     */
    public function set(PluginsBuiltinSetRequest|array $params): void
    {
        $paramsArray = ($params instanceof PluginsBuiltinSetRequest
            ? $params
            : PluginsBuiltinSetRequest::fromArray($params))->toArray();

        $this->client->request('plugins.builtin.set', $paramsArray);
    }
}
