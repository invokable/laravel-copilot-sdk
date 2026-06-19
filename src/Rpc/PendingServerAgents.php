<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AgentDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\AgentsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\ServerAgentList;

/**
 * Pending server-level agents RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerAgents
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Discover custom agents across user, project, plugin, and remote sources.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function discover(AgentsDiscoverRequest|array $params = []): ServerAgentList
    {
        $paramsArray = ($params instanceof AgentsDiscoverRequest
            ? $params
            : AgentsDiscoverRequest::fromArray($params))->toArray();

        return ServerAgentList::fromArray(
            $this->client->request('agents.discover', $paramsArray),
        );
    }

    /**
     * Returns the canonical directories where a client may create custom agents that the
     * runtime will recognize, including ones that do not exist yet.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function getDiscoveryPaths(AgentsGetDiscoveryPathsRequest|array $params = []): AgentDiscoveryPathList
    {
        $paramsArray = ($params instanceof AgentsGetDiscoveryPathsRequest
            ? $params
            : AgentsGetDiscoveryPathsRequest::fromArray($params))->toArray();

        return AgentDiscoveryPathList::fromArray(
            $this->client->request('agents.getDiscoveryPaths', $paramsArray),
        );
    }
}
