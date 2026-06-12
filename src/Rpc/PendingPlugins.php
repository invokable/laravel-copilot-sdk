<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PluginList;
use Revolution\Copilot\Types\Rpc\PluginsReloadRequest;

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
    public function list(): PluginList
    {
        return PluginList::fromArray(
            $this->client->request('session.plugins.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Reload plugins.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function reload(PluginsReloadRequest|array|null $params = null): void
    {
        $paramsArray = match (true) {
            $params instanceof PluginsReloadRequest => $params->toArray(),
            is_array($params) => PluginsReloadRequest::fromArray($params)->toArray(),
            default => [],
        };
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.plugins.reload', $paramsArray);
    }
}
