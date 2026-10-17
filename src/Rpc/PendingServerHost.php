<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\HostDisposeRequest;
use Revolution\Copilot\Types\Rpc\HostDisposeResult;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostListSessionsResult;
use Revolution\Copilot\Types\Rpc\HostPublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostPublishSessionResult;
use Revolution\Copilot\Types\Rpc\HostStartRequest;
use Revolution\Copilot\Types\Rpc\HostStartResult;
use Revolution\Copilot\Types\Rpc\HostUnpublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostUnpublishSessionResult;

/**
 * Pending host-level RPC operations.
 *
 * @experimental This API group may change or be removed.
 */
class PendingServerHost
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
     * Withdraw an application publication from an owned live host.
     *
     * The underlying local session and any ongoing work are preserved.
     */
    public function unpublishSession(HostUnpublishSessionRequest|array $params): HostUnpublishSessionResult
    {
        $paramsArray = $params instanceof HostUnpublishSessionRequest
            ? $params->toArray()
            : HostUnpublishSessionRequest::fromArray($params)->toArray();

        return HostUnpublishSessionResult::fromArray(
            $this->client->request('host.unpublishSession', $paramsArray),
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

    /**
     * Dispose a supervised host listener.
     */
    public function dispose(HostDisposeRequest|array $params): HostDisposeResult
    {
        $paramsArray = $params instanceof HostDisposeRequest
            ? $params->toArray()
            : HostDisposeRequest::fromArray($params)->toArray();

        return HostDisposeResult::fromArray(
            $this->client->request('host.dispose', $paramsArray),
        );
    }
}
