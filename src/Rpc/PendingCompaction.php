<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionCompactionCompactResult;

/**
 * Pending compaction RPC operations for a session.
 */
class PendingCompaction
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Compact the session history.
     */
    public function compact(): SessionCompactionCompactResult
    {
        return SessionCompactionCompactResult::fromArray(
            $this->client->request('session.compaction.compact', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
