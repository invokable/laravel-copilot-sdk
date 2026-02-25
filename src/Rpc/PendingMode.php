<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionModeGetResult;
use Revolution\Copilot\Types\Rpc\SessionModeSetParams;
use Revolution\Copilot\Types\Rpc\SessionModeSetResult;

/**
 * Pending mode RPC operations for a session.
 */
class PendingMode
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the current mode.
     */
    public function get(): SessionModeGetResult
    {
        return SessionModeGetResult::fromArray(
            $this->client->request('session.mode.get', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Set the mode.
     */
    public function set(SessionModeSetParams|array $params): SessionModeSetResult
    {
        $paramsArray = $params instanceof SessionModeSetParams ? $params->toArray() : $params;
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionModeSetResult::fromArray(
            $this->client->request('session.mode.set', $paramsArray),
        );
    }
}
