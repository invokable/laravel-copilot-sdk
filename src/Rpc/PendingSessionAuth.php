<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionAuthStatus;

/**
 * Pending session authentication RPC operations.
 */
class PendingSessionAuth
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the authentication status for this session.
     */
    public function getStatus(): SessionAuthStatus
    {
        return SessionAuthStatus::fromArray(
            $this->client->request('session.auth.getStatus', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
