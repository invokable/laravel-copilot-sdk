<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\DiscoveredExtensions;
use Revolution\Copilot\Types\Rpc\DiscoveredExtensionsDisableRequest;
use Revolution\Copilot\Types\Rpc\DiscoveredExtensionsEnableRequest;

/**
 * Pending server-level extension RPC operations.
 *
 * @experimental This API group is part of an experimental API and may change or be removed.
 */
class PendingServerExtensions
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Discover persisted user and plugin extensions.
     */
    public function discover(): DiscoveredExtensions
    {
        return DiscoveredExtensions::fromArray(
            $this->client->request('extensions.discover', []),
        );
    }

    /**
     * Persistently enable extension IDs for future sessions.
     */
    public function enable(DiscoveredExtensionsEnableRequest|array $params): void
    {
        $paramsArray = ($params instanceof DiscoveredExtensionsEnableRequest
            ? $params
            : DiscoveredExtensionsEnableRequest::fromArray($params))->toArray();

        $this->client->request('extensions.enable', $paramsArray);
    }

    /**
     * Persistently disable extension IDs for future sessions.
     */
    public function disable(DiscoveredExtensionsDisableRequest|array $params): void
    {
        $paramsArray = ($params instanceof DiscoveredExtensionsDisableRequest
            ? $params
            : DiscoveredExtensionsDisableRequest::fromArray($params))->toArray();

        $this->client->request('extensions.disable', $paramsArray);
    }
}
