<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\ModelListRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToResult;
use Revolution\Copilot\Types\Rpc\SessionModelList;

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

    /**
     * List models available to this session.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function list(ModelListRequest|array $params = []): SessionModelList
    {
        $paramsArray = ($params instanceof ModelListRequest ? $params : ModelListRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionModelList::fromArray(
            $this->client->request('session.model.list', $paramsArray),
        );
    }
}
