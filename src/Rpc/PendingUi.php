<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationParams;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationResult;

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
    public function elicitation(SessionUiElicitationParams|array $params): SessionUiElicitationResult
    {
        $paramsArray = ($params instanceof SessionUiElicitationParams ? $params : SessionUiElicitationParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionUiElicitationResult::fromArray(
            $this->client->request('session.ui.elicitation', $paramsArray),
        );
    }
}
