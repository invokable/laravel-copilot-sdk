<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\InstructionsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\InstructionsGetSourcesResult;
use Revolution\Copilot\Types\Rpc\ServerInstructionSourceList;

/**
 * Pending instructions RPC operations.
 */
class PendingInstructions
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the instruction sources for this session.
     */
    public function getSources(): InstructionsGetSourcesResult
    {
        return InstructionsGetSourcesResult::fromArray(
            $this->client->request('session.instructions.getSources', ['sessionId' => $this->sessionId]),
        );
    }

    /**
     * Discover instruction sources across user, repository, and plugin sources.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function discover(InstructionsDiscoverRequest|array $params = []): ServerInstructionSourceList
    {
        $paramsArray = ($params instanceof InstructionsDiscoverRequest ? $params : InstructionsDiscoverRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ServerInstructionSourceList::fromArray(
            $this->client->request('instructions.discover', $paramsArray),
        );
    }
}
