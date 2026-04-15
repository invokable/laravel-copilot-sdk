<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionsForkRequest;
use Revolution\Copilot\Types\Rpc\SessionsForkResult;

/**
 * Pending sessions RPC operations (server-scoped).
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSessions
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Fork a session.
     *
     * Creates a new session that includes events from the source session.
     * Optionally, provide a toEventId to include only events before that ID.
     */
    public function fork(SessionsForkRequest|array $params): SessionsForkResult
    {
        $paramsArray = ($params instanceof SessionsForkRequest ? $params : SessionsForkRequest::fromArray($params))->toArray();

        return SessionsForkResult::fromArray(
            $this->client->request('sessions.fork', $paramsArray),
        );
    }
}
