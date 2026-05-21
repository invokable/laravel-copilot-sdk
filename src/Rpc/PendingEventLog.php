<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\EventLogReadRequest;
use Revolution\Copilot\Types\Rpc\EventLogReleaseInterestResult;
use Revolution\Copilot\Types\Rpc\EventLogTailResult;
use Revolution\Copilot\Types\Rpc\EventsReadResult;
use Revolution\Copilot\Types\Rpc\RegisterEventInterestParams;
use Revolution\Copilot\Types\Rpc\RegisterEventInterestResult;
use Revolution\Copilot\Types\Rpc\ReleaseEventInterestParams;

/**
 * Pending session-scoped event log RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingEventLog
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function read(EventLogReadRequest|array $params): EventsReadResult
    {
        $paramsArray = ($params instanceof EventLogReadRequest ? $params : EventLogReadRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return EventsReadResult::fromArray(
            $this->client->request('session.eventLog.read', $paramsArray),
        );
    }

    public function tail(): EventLogTailResult
    {
        return EventLogTailResult::fromArray(
            $this->client->request('session.eventLog.tail', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    public function registerInterest(RegisterEventInterestParams|array $params): RegisterEventInterestResult
    {
        $paramsArray = ($params instanceof RegisterEventInterestParams ? $params : RegisterEventInterestParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return RegisterEventInterestResult::fromArray(
            $this->client->request('session.eventLog.registerInterest', $paramsArray),
        );
    }

    public function releaseInterest(ReleaseEventInterestParams|array $params): EventLogReleaseInterestResult
    {
        $paramsArray = ($params instanceof ReleaseEventInterestParams ? $params : ReleaseEventInterestParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return EventLogReleaseInterestResult::fromArray(
            $this->client->request('session.eventLog.releaseInterest', $paramsArray),
        );
    }
}
