<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveGetStateResult;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveSetStayInAutopilotRequest;

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

    /**
     * Prevent a request to return to interactive mode when the objective completes or pauses.
     */
    public function setStayInAutopilot(AutopilotObjectiveSetStayInAutopilotRequest|array $params): void
    {
        $paramsArray = ($params instanceof AutopilotObjectiveSetStayInAutopilotRequest
            ? $params
            : AutopilotObjectiveSetStayInAutopilotRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.autopilotObjective.setStayInAutopilot', $paramsArray);
    }
}
