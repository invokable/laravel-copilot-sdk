<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CloudTasksCreateParams;
use Revolution\Copilot\Types\Rpc\CloudTasksCreateResult;
use Revolution\Copilot\Types\Rpc\CloudTasksDeleteParams;
use Revolution\Copilot\Types\Rpc\CloudTasksDeleteResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetParams;
use Revolution\Copilot\Types\Rpc\CloudTasksGetResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionLogsParams;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionLogsResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionReplayParams;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSessionReplayResult;
use Revolution\Copilot\Types\Rpc\CloudTasksGetSharingStatusParams;
use Revolution\Copilot\Types\Rpc\CloudTasksResolveTaskIdParams;
use Revolution\Copilot\Types\Rpc\CloudTasksResolveTaskIdResult;
use Revolution\Copilot\Types\Rpc\CloudTasksSetSharingStatusParams;
use Revolution\Copilot\Types\Rpc\CloudTasksSharingStatusResult;

/**
 * Pending cloud coding-agent task RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerCloudTasks
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Starts a cloud coding-agent task.
     */
    public function create(CloudTasksCreateParams|array $params): CloudTasksCreateResult
    {
        $paramsArray = ($params instanceof CloudTasksCreateParams
            ? $params
            : CloudTasksCreateParams::fromArray($params))->toArray();

        return CloudTasksCreateResult::fromArray(
            $this->client->request('cloudTasks.create', $paramsArray),
        );
    }

    /**
     * Soft-deletes a cloud coding-agent task.
     */
    public function delete(CloudTasksDeleteParams|array $params): CloudTasksDeleteResult
    {
        $paramsArray = ($params instanceof CloudTasksDeleteParams
            ? $params
            : CloudTasksDeleteParams::fromArray($params))->toArray();

        return CloudTasksDeleteResult::fromArray(
            $this->client->request('cloudTasks.delete', $paramsArray),
        );
    }

    /**
     * Reads a cloud coding-agent task.
     */
    public function get(CloudTasksGetParams|array $params): CloudTasksGetResult
    {
        $paramsArray = ($params instanceof CloudTasksGetParams
            ? $params
            : CloudTasksGetParams::fromArray($params))->toArray();

        return CloudTasksGetResult::fromArray(
            $this->client->request('cloudTasks.get', $paramsArray),
        );
    }

    /**
     * Reads a cloud coding-agent session's logs.
     */
    public function getSessionLogs(CloudTasksGetSessionLogsParams|array $params): CloudTasksGetSessionLogsResult
    {
        $paramsArray = ($params instanceof CloudTasksGetSessionLogsParams
            ? $params
            : CloudTasksGetSessionLogsParams::fromArray($params))->toArray();

        return CloudTasksGetSessionLogsResult::fromArray(
            $this->client->request('cloudTasks.getSessionLogs', $paramsArray),
        );
    }

    /**
     * Reconstructs a cloud coding-agent session's recorded events.
     */
    public function getSessionReplay(CloudTasksGetSessionReplayParams|array $params): CloudTasksGetSessionReplayResult
    {
        $paramsArray = ($params instanceof CloudTasksGetSessionReplayParams
            ? $params
            : CloudTasksGetSessionReplayParams::fromArray($params))->toArray();

        return CloudTasksGetSessionReplayResult::fromArray(
            $this->client->request('cloudTasks.getSessionReplay', $paramsArray),
        );
    }

    /**
     * Reads a cloud coding-agent task's sharing status.
     */
    public function getSharingStatus(CloudTasksGetSharingStatusParams|array $params): CloudTasksSharingStatusResult
    {
        $paramsArray = ($params instanceof CloudTasksGetSharingStatusParams
            ? $params
            : CloudTasksGetSharingStatusParams::fromArray($params))->toArray();

        return CloudTasksSharingStatusResult::fromArray(
            $this->client->request('cloudTasks.getSharingStatus', $paramsArray),
        );
    }

    /**
     * Changes a cloud coding-agent task's sharing status.
     */
    public function setSharingStatus(CloudTasksSetSharingStatusParams|array $params): CloudTasksSharingStatusResult
    {
        $paramsArray = ($params instanceof CloudTasksSetSharingStatusParams
            ? $params
            : CloudTasksSetSharingStatusParams::fromArray($params))->toArray();

        return CloudTasksSharingStatusResult::fromArray(
            $this->client->request('cloudTasks.setSharingStatus', $paramsArray),
        );
    }

    /**
     * Resolves the session a caller should open for a cloud coding-agent task.
     */
    public function resolveTaskId(CloudTasksResolveTaskIdParams|array $params): CloudTasksResolveTaskIdResult
    {
        $paramsArray = ($params instanceof CloudTasksResolveTaskIdParams
            ? $params
            : CloudTasksResolveTaskIdParams::fromArray($params))->toArray();

        return CloudTasksResolveTaskIdResult::fromArray(
            $this->client->request('cloudTasks.resolveTaskId', $paramsArray),
        );
    }
}
