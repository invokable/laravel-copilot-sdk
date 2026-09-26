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

    /**
     * Install, update, or change the state of a plugin.
     *
     * These protocol payloads evolve independently of the stable plugin-list
     * response, so they are passed through as JSON-compatible arrays.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function install(array $params): array
    {
        return $this->request('session.plugins.install', $params);
    }

    public function uninstall(array $params): void
    {
        $this->notify('session.plugins.uninstall', $params);
    }

    public function update(array $params): array
    {
        return $this->request('session.plugins.update', $params);
    }

    public function enable(array $params): void
    {
        $this->notify('session.plugins.enable', $params);
    }

    public function disable(array $params): void
    {
        $this->notify('session.plugins.disable', $params);
    }

    public function marketplaces(): PendingMarketplaces
    {
        return new PendingMarketplaces($this->client, $this->sessionId);
    }

    protected function request(string $method, array $params): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request($method, $params);
    }

    protected function notify(string $method, array $params): void
    {
        $params['sessionId'] = $this->sessionId;

        $this->client->request($method, $params);
    }
}
