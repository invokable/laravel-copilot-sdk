<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsClassifyTrajectoryRequest;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsClassifyTrajectoryResult;

/**
 * Experimental server-level task analytics operations.
 *
 * @experimental
 */
class PendingServerTaskAnalytics
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Classify a recorded Copilot or ATIF trajectory.
     *
     * @experimental Supplied content may be sent to GitHub CAPI without automatic redaction or
     *               prompt-injection sanitization. Classification results are untrusted.
     */
    public function classifyTrajectory(TaskAnalyticsClassifyTrajectoryRequest|array $params): TaskAnalyticsClassifyTrajectoryResult
    {
        $paramsArray = ($params instanceof TaskAnalyticsClassifyTrajectoryRequest
            ? $params
            : TaskAnalyticsClassifyTrajectoryRequest::fromArray($params))->toArray();

        return TaskAnalyticsClassifyTrajectoryResult::fromArray(
            $this->client->request('taskAnalytics.classifyTrajectory', $paramsArray),
        );
    }
}
