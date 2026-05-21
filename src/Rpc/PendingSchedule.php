<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ScheduleList;
use Revolution\Copilot\Types\Rpc\ScheduleStopRequest;
use Revolution\Copilot\Types\Rpc\ScheduleStopResult;

/**
 * Pending schedule RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSchedule
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Lists the session's currently active scheduled prompts.
     */
    public function list(): ScheduleList
    {
        return ScheduleList::fromArray(
            $this->client->request('session.schedule.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Removes a scheduled prompt by id.
     */
    public function stop(ScheduleStopRequest|array $params): ScheduleStopResult
    {
        $paramsArray = ($params instanceof ScheduleStopRequest ? $params : ScheduleStopRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ScheduleStopResult::fromArray(
            $this->client->request('session.schedule.stop', $paramsArray),
        );
    }
}
