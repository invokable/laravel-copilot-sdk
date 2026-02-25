<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionModelGetCurrentResult;
use Revolution\Copilot\Types\Rpc\SessionModelSwitchToParams;
use Revolution\Copilot\Types\Rpc\SessionModelSwitchToResult;

/**
 * Pending model RPC operations for a session.
 */
class PendingModel
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the current model for this session.
     */
    public function getCurrent(): SessionModelGetCurrentResult
    {
        return SessionModelGetCurrentResult::fromArray(
            $this->client->request('session.model.getCurrent', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Switch to a different model.
     */
    public function switchTo(SessionModelSwitchToParams|array $params): SessionModelSwitchToResult
    {
        $paramsArray = $params instanceof SessionModelSwitchToParams ? $params->toArray() : $params;
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionModelSwitchToResult::fromArray(
            $this->client->request('session.model.switchTo', $paramsArray),
        );
    }
}
