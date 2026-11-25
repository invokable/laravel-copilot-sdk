<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ContextBoardGetConsolidationPromptResult;

/**
 * Pending server-scoped context board RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerContextBoard
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Get the runtime's instructions for the context board consolidation agent.
     *
     * @experimental This API is experimental and may change or be removed.
     */
    public function getConsolidationPrompt(): ContextBoardGetConsolidationPromptResult
    {
        return ContextBoardGetConsolidationPromptResult::fromArray(
            $this->client->request('contextBoard.getConsolidationPrompt', []),
        );
    }
}
