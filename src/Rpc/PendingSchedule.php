<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ScheduleAddAtRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddCronRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddResult;
use Revolution\Copilot\Types\Rpc\ScheduleAddSelfPacedRequest;
use Revolution\Copilot\Types\Rpc\ScheduleHasSelfPacedResult;
use Revolution\Copilot\Types\Rpc\ScheduleList;
use Revolution\Copilot\Types\Rpc\ScheduleRearmSelfPacedRequest;
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

    /**
     * Hydrates the native schedule registry from persisted session events.
     */
    public function hydrate(): void
    {
        $this->client->request('session.schedule.hydrate', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Registers a relative-interval scheduled prompt.
     */
    public function add(ScheduleAddRequest|array $params): ScheduleAddResult
    {
        $paramsArray = ($params instanceof ScheduleAddRequest ? $params : ScheduleAddRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ScheduleAddResult::fromArray(
            $this->client->request('session.schedule.add', $paramsArray),
        );
    }

    /**
     * Registers a recurring cron scheduled prompt.
     */
    public function addCron(ScheduleAddCronRequest|array $params): ScheduleAddResult
    {
        $paramsArray = ($params instanceof ScheduleAddCronRequest ? $params : ScheduleAddCronRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ScheduleAddResult::fromArray(
            $this->client->request('session.schedule.addCron', $paramsArray),
        );
    }

    /**
     * Registers an absolute-time scheduled prompt.
     */
    public function addAt(ScheduleAddAtRequest|array $params): ScheduleAddResult
    {
        $paramsArray = ($params instanceof ScheduleAddAtRequest ? $params : ScheduleAddAtRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ScheduleAddResult::fromArray(
            $this->client->request('session.schedule.addAt', $paramsArray),
        );
    }

    /**
     * Registers a self-paced scheduled prompt.
     */
    public function addSelfPaced(ScheduleAddSelfPacedRequest|array $params): ScheduleAddResult
    {
        $paramsArray = ($params instanceof ScheduleAddSelfPacedRequest ? $params : ScheduleAddSelfPacedRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ScheduleAddResult::fromArray(
            $this->client->request('session.schedule.addSelfPaced', $paramsArray),
        );
    }

    /**
     * Returns whether the session has an active self-paced schedule.
     */
    public function hasSelfPaced(): ScheduleHasSelfPacedResult
    {
        return ScheduleHasSelfPacedResult::fromArray(
            $this->client->request('session.schedule.hasSelfPaced', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Re-arms an active self-paced scheduled prompt.
     */
    public function rearmSelfPaced(ScheduleRearmSelfPacedRequest|array $params): ScheduleAddResult
    {
        $paramsArray = ($params instanceof ScheduleRearmSelfPacedRequest
            ? $params
            : ScheduleRearmSelfPacedRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ScheduleAddResult::fromArray(
            $this->client->request('session.schedule.rearmSelfPaced', $paramsArray),
        );
    }
}
