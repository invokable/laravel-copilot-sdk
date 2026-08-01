<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionPredictRequest;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionResult;

/**
 * Pending session limit prediction RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingLimitPrediction
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Predicts an AI-credit session limit for the session's resolved model.
     *
     * Returns an unavailable result instead of falling back when the current
     * model is unresolved auto. Omitting `modelId` uses the session's currently
     * selected model.
     */
    public function predict(SessionLimitPredictionPredictRequest|array|null $params = null): SessionLimitPredictionResult
    {
        $params ??= new SessionLimitPredictionPredictRequest;

        $paramsArray = ($params instanceof SessionLimitPredictionPredictRequest ? $params : SessionLimitPredictionPredictRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionLimitPredictionResult::fromArray(
            $this->client->request('session.limitPrediction.predict', $paramsArray),
        );
    }
}
