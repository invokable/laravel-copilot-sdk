<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\UIElicitationRequest;
use Revolution\Copilot\Types\Rpc\UIElicitationResponse;
use Revolution\Copilot\Types\Rpc\UIElicitationResult;
use Revolution\Copilot\Types\Rpc\UIHandlePendingElicitationRequest;
use Revolution\Copilot\Types\Rpc\UIHandlePendingSessionLimitsExhaustedRequest;

/**
 * Pending UI RPC operations for a session.
 */
class PendingUi
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Respond to a UI elicitation request.
     */
    public function elicitation(UIElicitationRequest|array $params): UIElicitationResponse
    {
        $paramsArray = ($params instanceof UIElicitationRequest ? $params : UIElicitationRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return UIElicitationResponse::fromArray(
            $this->client->request('session.ui.elicitation', $paramsArray),
        );
    }

    /**
     * Respond to a pending elicitation request from a broadcast event.
     */
    public function handlePendingElicitation(UIHandlePendingElicitationRequest|array $params): UIElicitationResult
    {
        $paramsArray = ($params instanceof UIHandlePendingElicitationRequest ? $params : UIHandlePendingElicitationRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return UIElicitationResult::fromArray(
            $this->client->request('session.ui.handlePendingElicitation', $paramsArray),
        );
    }

    /**
     * Resolves a pending session_limits_exhausted.requested event with the user's selected limit action.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function handlePendingSessionLimitsExhausted(UIHandlePendingSessionLimitsExhaustedRequest|array $params): UIElicitationResult
    {
        $paramsArray = ($params instanceof UIHandlePendingSessionLimitsExhaustedRequest ? $params : UIHandlePendingSessionLimitsExhaustedRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return UIElicitationResult::fromArray(
            $this->client->request('session.ui.handlePendingSessionLimitsExhausted', $paramsArray),
        );
    }
}
