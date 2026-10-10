<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostListSessionsResult;
use Revolution\Copilot\Types\Rpc\HostPublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostPublishSessionResult;
use Revolution\Copilot\Types\Rpc\HostStartRequest;
use Revolution\Copilot\Types\Rpc\HostStartResult;

/**
 * Pending host-level RPC operations.
 *
 * @experimental This API group may change or be removed.
 */
class PendingHost
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Start a supervised host listener.
     */
    public function start(HostStartRequest|array $params): HostStartResult
    {
        $paramsArray = $params instanceof HostStartRequest
            ? $params->toArray()
            : HostStartRequest::fromArray($params)->toArray();

        return HostStartResult::fromArray(
            $this->client->request('host.start', $paramsArray),
        );
    }

    /**
     * Publish a resident session into a host catalog.
     */
    public function publishSession(HostPublishSessionRequest|array $params): HostPublishSessionResult
    {
        $paramsArray = $params instanceof HostPublishSessionRequest
            ? $params->toArray()
            : HostPublishSessionRequest::fromArray($params)->toArray();

        return HostPublishSessionResult::fromArray(
            $this->client->request('host.publishSession', $paramsArray),
        );
    }

    /**
     * Read the advertised live and dormant session catalog without subscribing.
     */
    public function listSessions(HostListSessionsRequest|array $params = []): HostListSessionsResult
    {
        $paramsArray = $params instanceof HostListSessionsRequest
            ? $params->toArray()
            : HostListSessionsRequest::fromArray($params)->toArray();

        return HostListSessionsResult::fromArray(
            $this->client->request('host.listSessions', $paramsArray),
        );
    }
}
