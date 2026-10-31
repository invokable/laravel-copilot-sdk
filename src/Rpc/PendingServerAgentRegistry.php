<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AgentRegistryEnsureDirRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryEnsureDirResult;
use Revolution\Copilot\Types\Rpc\AgentRegistryListEntriesRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryListEntriesResult;
use Revolution\Copilot\Types\Rpc\AgentRegistryRemoveEntryRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryRemoveEntryResult;

/**
 * Pending local agent registry RPC operations.
 *
 * @experimental This API group may change or be removed.
 */
class PendingServerAgentRegistry
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Create the local agent registry directory with restricted permissions.
     *
     * @experimental
     */
    public function ensureDir(AgentRegistryEnsureDirRequest|array $params): AgentRegistryEnsureDirResult
    {
        $paramsArray = ($params instanceof AgentRegistryEnsureDirRequest
            ? $params
            : AgentRegistryEnsureDirRequest::fromArray($params))->toArray();

        return AgentRegistryEnsureDirResult::fromArray(
            $this->client->request('agentRegistry.ensureDir', $paramsArray),
        );
    }

    /**
     * List live local agents without connection credentials.
     *
     * @experimental
     */
    public function listEntries(AgentRegistryListEntriesRequest|array $params): AgentRegistryListEntriesResult
    {
        $paramsArray = ($params instanceof AgentRegistryListEntriesRequest
            ? $params
            : AgentRegistryListEntriesRequest::fromArray($params))->toArray();

        return AgentRegistryListEntriesResult::fromArray(
            $this->client->request('agentRegistry.listEntries', $paramsArray),
        );
    }

    /**
     * Remove a local agent entry. An absent entry succeeds.
     *
     * @experimental
     */
    public function removeEntry(AgentRegistryRemoveEntryRequest|array $params): AgentRegistryRemoveEntryResult
    {
        $paramsArray = ($params instanceof AgentRegistryRemoveEntryRequest
            ? $params
            : AgentRegistryRemoveEntryRequest::fromArray($params))->toArray();

        return AgentRegistryRemoveEntryResult::fromArray(
            $this->client->request('agentRegistry.removeEntry', $paramsArray),
        );
    }
}
