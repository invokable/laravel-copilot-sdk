<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\TaskList;
use Revolution\Copilot\Types\Rpc\TasksCancelRequest;
use Revolution\Copilot\Types\Rpc\TasksCancelResult;
use Revolution\Copilot\Types\Rpc\TasksGetCurrentPromotableResult;
use Revolution\Copilot\Types\Rpc\TasksGetProgressRequest;
use Revolution\Copilot\Types\Rpc\TasksGetProgressResult;
use Revolution\Copilot\Types\Rpc\TasksPromoteCurrentToBackgroundResult;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundRequest;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundResult;
use Revolution\Copilot\Types\Rpc\TasksReadOutputRequest;
use Revolution\Copilot\Types\Rpc\TasksReadOutputResult;
use Revolution\Copilot\Types\Rpc\TasksRefreshResult;
use Revolution\Copilot\Types\Rpc\TasksRegisterRequest;
use Revolution\Copilot\Types\Rpc\TasksRegisterResult;
use Revolution\Copilot\Types\Rpc\TasksRemoveRequest;
use Revolution\Copilot\Types\Rpc\TasksRemoveResult;
use Revolution\Copilot\Types\Rpc\TasksSendMessageRequest;
use Revolution\Copilot\Types\Rpc\TasksSendMessageResult;
use Revolution\Copilot\Types\Rpc\TasksStartAgentRequest;
use Revolution\Copilot\Types\Rpc\TasksStartAgentResult;
use Revolution\Copilot\Types\Rpc\TasksUpdateRequest;
use Revolution\Copilot\Types\Rpc\TasksUpdateResult;
use Revolution\Copilot\Types\Rpc\TasksWaitForPendingResult;

/**
 * Pending tasks RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingTasks
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Start a background agent task.
     */
    public function startAgent(TasksStartAgentRequest|array $params): TasksStartAgentResult
    {
        $paramsArray = ($params instanceof TasksStartAgentRequest ? $params : TasksStartAgentRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksStartAgentResult::fromArray(
            $this->client->request('session.tasks.startAgent', $paramsArray),
        );
    }

    /**
     * List all currently tracked tasks.
     */
    public function list(): TaskList
    {
        return TaskList::fromArray(
            $this->client->request('session.tasks.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Read incremental output from a detached shell task.
     *
     * @experimental This API is experimental and may change or be removed.
     */
    public function readOutput(TasksReadOutputRequest|array $params): TasksReadOutputResult
    {
        $paramsArray = ($params instanceof TasksReadOutputRequest ? $params : TasksReadOutputRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksReadOutputResult::fromArray(
            $this->client->request('session.tasks.readOutput', $paramsArray),
        );
    }

    /**
     * Register or reclaim a client-owned task.
     */
    public function register(TasksRegisterRequest|array $params): TasksRegisterResult
    {
        $paramsArray = ($params instanceof TasksRegisterRequest ? $params : TasksRegisterRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksRegisterResult::fromArray(
            $this->client->request('session.tasks.register', $paramsArray),
        );
    }

    /**
     * Publish progress or a terminal outcome for a client-owned task.
     */
    public function update(TasksUpdateRequest|array $params): TasksUpdateResult
    {
        $paramsArray = ($params instanceof TasksUpdateRequest ? $params : TasksUpdateRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksUpdateResult::fromArray(
            $this->client->request('session.tasks.update', $paramsArray),
        );
    }

    /**
     * Get progress information for a tracked task.
     */
    public function getProgress(TasksGetProgressRequest|array $params): TasksGetProgressResult
    {
        $paramsArray = ($params instanceof TasksGetProgressRequest ? $params : TasksGetProgressRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksGetProgressResult::fromArray(
            $this->client->request('session.tasks.getProgress', $paramsArray),
        );
    }

    /**
     * Refresh metadata for detached background shells.
     */
    public function refresh(): TasksRefreshResult
    {
        return TasksRefreshResult::fromArray(
            $this->client->request('session.tasks.refresh', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Wait for in-flight background tasks and their follow-up turns to settle.
     */
    public function waitForPending(): TasksWaitForPendingResult
    {
        return TasksWaitForPendingResult::fromArray(
            $this->client->request('session.tasks.waitForPending', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Get the first task currently eligible for background promotion.
     */
    public function getCurrentPromotable(): TasksGetCurrentPromotableResult
    {
        return TasksGetCurrentPromotableResult::fromArray(
            $this->client->request('session.tasks.getCurrentPromotable', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Promote the first eligible task to background mode.
     */
    public function promoteCurrentToBackground(): TasksPromoteCurrentToBackgroundResult
    {
        return TasksPromoteCurrentToBackgroundResult::fromArray(
            $this->client->request('session.tasks.promoteCurrentToBackground', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Promote a task from sync to background mode.
     */
    public function promoteToBackground(TasksPromoteToBackgroundRequest|array $params): TasksPromoteToBackgroundResult
    {
        $paramsArray = ($params instanceof TasksPromoteToBackgroundRequest ? $params : TasksPromoteToBackgroundRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksPromoteToBackgroundResult::fromArray(
            $this->client->request('session.tasks.promoteToBackground', $paramsArray),
        );
    }

    /**
     * Cancel a running or idle task.
     */
    public function cancel(TasksCancelRequest|array $params): TasksCancelResult
    {
        $paramsArray = ($params instanceof TasksCancelRequest ? $params : TasksCancelRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksCancelResult::fromArray(
            $this->client->request('session.tasks.cancel', $paramsArray),
        );
    }

    /**
     * Remove a completed or cancelled task.
     */
    public function remove(TasksRemoveRequest|array $params): TasksRemoveResult
    {
        $paramsArray = ($params instanceof TasksRemoveRequest ? $params : TasksRemoveRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksRemoveResult::fromArray(
            $this->client->request('session.tasks.remove', $paramsArray),
        );
    }

    /**
     * Send a message to a running agent task.
     */
    public function sendMessage(TasksSendMessageRequest|array $params): TasksSendMessageResult
    {
        $paramsArray = ($params instanceof TasksSendMessageRequest ? $params : TasksSendMessageRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return TasksSendMessageResult::fromArray(
            $this->client->request('session.tasks.sendMessage', $paramsArray),
        );
    }
}
