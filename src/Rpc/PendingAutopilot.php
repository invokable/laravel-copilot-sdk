<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AutopilotEvaluateIdleContinuationRequest;
use Revolution\Copilot\Types\Rpc\AutopilotEvaluateIdleContinuationResult;

/**
 * Internal Autopilot RPC operations for a session.
 *
 * @internal
 */
class PendingAutopilot
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Evaluates the runtime-owned Autopilot continuation policy at session idle.
     *
     * @internal
     */
    public function evaluateIdleContinuation(): AutopilotEvaluateIdleContinuationResult
    {
        $params = (new AutopilotEvaluateIdleContinuationRequest)->toArray();
        $params['sessionId'] = $this->sessionId;

        return AutopilotEvaluateIdleContinuationResult::fromArray(
            $this->client->request('session.autopilot.evaluateIdleContinuation', $params),
        );
    }
}
