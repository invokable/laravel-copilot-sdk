<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\UIElicitationRequest;
use Revolution\Copilot\Types\Rpc\UIElicitationResponse;
use Revolution\Copilot\Types\Rpc\UIElicitationResult;
use Revolution\Copilot\Types\Rpc\UIHandlePendingElicitationRequest;

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
}
