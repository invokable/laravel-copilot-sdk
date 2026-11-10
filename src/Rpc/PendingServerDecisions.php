<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\DecisionsAskRequest;
use Revolution\Copilot\Types\Rpc\DecisionsAskResult;

/**
 * Pending decisions RPC operations.
 *
 * @experimental
 */
class PendingServerDecisions
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Ask a decision model to answer questions about shared context.
     */
    public function ask(DecisionsAskRequest|array $params): DecisionsAskResult
    {
        $paramsArray = $params instanceof DecisionsAskRequest
            ? $params->toArray()
            : DecisionsAskRequest::fromArray($params)->toArray();

        return DecisionsAskResult::fromArray(
            $this->client->request('decisions.ask', $paramsArray),
        );
    }
}
