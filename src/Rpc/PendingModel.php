<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToResult;

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
    public function getCurrent(): CurrentModel
    {
        return CurrentModel::fromArray(
            $this->client->request('session.model.getCurrent', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Switch to a different model.
     */
    public function switchTo(ModelSwitchToRequest|array $params): ModelSwitchToResult
    {
        $paramsArray = ($params instanceof ModelSwitchToRequest ? $params : ModelSwitchToRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ModelSwitchToResult::fromArray(
            $this->client->request('session.model.switchTo', $paramsArray),
        );
    }
}
