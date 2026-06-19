<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\InstructionDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\InstructionsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\InstructionsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\ServerInstructionSourceList;

/**
 * Pending server-level instructions RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerInstructions
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Discover instruction sources across user, repository, and plugin sources.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function discover(InstructionsDiscoverRequest|array $params = []): ServerInstructionSourceList
    {
        $paramsArray = ($params instanceof InstructionsDiscoverRequest
            ? $params
            : InstructionsDiscoverRequest::fromArray($params))->toArray();

        return ServerInstructionSourceList::fromArray(
            $this->client->request('instructions.discover', $paramsArray),
        );
    }

    /**
     * Returns the canonical files and directories where a client may create custom instructions
     * that the runtime will recognize, including ones that do not exist yet.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function getDiscoveryPaths(InstructionsGetDiscoveryPathsRequest|array $params = []): InstructionDiscoveryPathList
    {
        $paramsArray = ($params instanceof InstructionsGetDiscoveryPathsRequest
            ? $params
            : InstructionsGetDiscoveryPathsRequest::fromArray($params))->toArray();

        return InstructionDiscoveryPathList::fromArray(
            $this->client->request('instructions.getDiscoveryPaths', $paramsArray),
        );
    }
}
