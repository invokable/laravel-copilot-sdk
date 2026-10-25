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

    /**
     * Reload instruction sources for this session.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function reload(): void
    {
        $this->client->request('session.instructions.reload', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
