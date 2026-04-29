<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\TaskList;
use Revolution\Copilot\Types\Rpc\TasksCancelRequest;
use Revolution\Copilot\Types\Rpc\TasksCancelResult;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundRequest;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundResult;
use Revolution\Copilot\Types\Rpc\TasksRemoveRequest;
use Revolution\Copilot\Types\Rpc\TasksRemoveResult;
use Revolution\Copilot\Types\Rpc\TasksStartAgentRequest;
use Revolution\Copilot\Types\Rpc\TasksStartAgentResult;

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
}
