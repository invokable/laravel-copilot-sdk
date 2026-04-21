<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\InstructionsGetSourcesResult;

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
}
