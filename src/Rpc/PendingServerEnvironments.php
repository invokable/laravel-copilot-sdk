<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\EnvironmentsDeleteRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsDeleteResult;
use Revolution\Copilot\Types\Rpc\EnvironmentsGetRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsGetResult;
use Revolution\Copilot\Types\Rpc\EnvironmentsListRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsListResult;

/**
 * Pending server-scoped GitHub Mission Control environment RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerEnvironments
{
    public function __construct(protected JsonRpcClient $client) {}

    /**
     * Lists GitHub Mission Control environments visible to the authenticated identity.
     */
    public function list(EnvironmentsListRequest|array $params = []): EnvironmentsListResult
    {
        $params = $params instanceof EnvironmentsListRequest ? $params : EnvironmentsListRequest::fromArray($params);

        return EnvironmentsListResult::fromArray(
            $this->client->request('environments.list', $params->toArray()),
        );
    }

    /**
     * Gets safe discovery information for a GitHub Mission Control environment.
     */
    public function get(EnvironmentsGetRequest|array $params): EnvironmentsGetResult
    {
        $params = $params instanceof EnvironmentsGetRequest ? $params : EnvironmentsGetRequest::fromArray($params);

        return EnvironmentsGetResult::fromArray(
            $this->client->request('environments.get', $params->toArray()),
        );
    }

    /**
     * Deletes a user-managed GitHub Mission Control environment.
     */
    public function delete(EnvironmentsDeleteRequest|array $params): EnvironmentsDeleteResult
    {
        $params = $params instanceof EnvironmentsDeleteRequest ? $params : EnvironmentsDeleteRequest::fromArray($params);

        return EnvironmentsDeleteResult::fromArray(
            $this->client->request('environments.delete', $params->toArray()),
        );
    }
}
