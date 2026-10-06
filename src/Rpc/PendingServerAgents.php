<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AgentDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\AgentsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\AgentsCustomAgentInitialModelDecisionRequest;
use Revolution\Copilot\Types\Rpc\AgentsCustomAgentInitialModelDecisionResult;
use Revolution\Copilot\Types\Rpc\AgentsGetAvailableBuiltinsRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetAvailableBuiltinsResult;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinDefinitionRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinDefinitionResult;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinListingDefinitionRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinListingDefinitionResult;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinsResult;
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

    /**
     * Returns the names and toggleability of agents shipped by the runtime.
     */
    public function getBuiltins(): AgentsGetBuiltinsResult
    {
        return AgentsGetBuiltinsResult::fromArray(
            $this->client->request('agents.getBuiltins', []),
        );
    }

    /**
     * Returns built-in agents available in the supplied feature-flag and runtime context.
     */
    public function getAvailableBuiltins(AgentsGetAvailableBuiltinsRequest|array $params = []): AgentsGetAvailableBuiltinsResult
    {
        $paramsArray = ($params instanceof AgentsGetAvailableBuiltinsRequest
            ? $params
            : AgentsGetAvailableBuiltinsRequest::fromArray($params))->toArray();

        return AgentsGetAvailableBuiltinsResult::fromArray(
            $this->client->request('agents.getAvailableBuiltins', $paramsArray),
        );
    }

    /**
     * Returns the YAML definition for a shipped built-in agent.
     */
    public function getBuiltinDefinition(AgentsGetBuiltinDefinitionRequest|array $params): AgentsGetBuiltinDefinitionResult
    {
        $paramsArray = ($params instanceof AgentsGetBuiltinDefinitionRequest
            ? $params
            : AgentsGetBuiltinDefinitionRequest::fromArray($params))->toArray();

        return AgentsGetBuiltinDefinitionResult::fromArray(
            $this->client->request('agents.getBuiltinDefinition', $paramsArray),
        );
    }

    /**
     * Returns the listing definition used to present a shipped built-in agent.
     */
    public function getBuiltinListingDefinition(AgentsGetBuiltinListingDefinitionRequest|array $params): AgentsGetBuiltinListingDefinitionResult
    {
        $paramsArray = ($params instanceof AgentsGetBuiltinListingDefinitionRequest
            ? $params
            : AgentsGetBuiltinListingDefinitionRequest::fromArray($params))->toArray();

        return AgentsGetBuiltinListingDefinitionResult::fromArray(
            $this->client->request('agents.getBuiltinListingDefinition', $paramsArray),
        );
    }

    /**
     * Applies the runtime's model-selection policy to a custom agent's model preferences.
     */
    public function customAgentInitialModelDecision(AgentsCustomAgentInitialModelDecisionRequest|array $params): AgentsCustomAgentInitialModelDecisionResult
    {
        $paramsArray = ($params instanceof AgentsCustomAgentInitialModelDecisionRequest
            ? $params
            : AgentsCustomAgentInitialModelDecisionRequest::fromArray($params))->toArray();

        return AgentsCustomAgentInitialModelDecisionResult::fromArray(
            $this->client->request('agents.customAgentInitialModelDecision', $paramsArray),
        );
    }
}
