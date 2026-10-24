<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveGetStateResult;

/**
 * Pending Autopilot objective RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingAutopilotObjective
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Read the current canonical Autopilot objective state for this session.
     */
    public function getState(): AutopilotObjectiveGetStateResult
    {
        return AutopilotObjectiveGetStateResult::fromArray(
            $this->client->request('session.autopilotObjective.getState', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
