<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionsForkRequest;
use Revolution\Copilot\Types\Rpc\SessionsForkResult;
use Revolution\Copilot\Types\Rpc\SessionsGetClientMetadataRequest;

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

    /**
     * Read client-owned metadata for multiple persisted local sessions without opening them.
     * Results preserve request order and report missing, corrupt, unsupported, or temporarily
     * unavailable sessions independently.
     *
     * @return array<int, array<string, mixed>> Ordered client metadata outcomes for the requested local sessions.
     */
    public function getClientMetadata(SessionsGetClientMetadataRequest|array $params): array
    {
        $paramsArray = ($params instanceof SessionsGetClientMetadataRequest ? $params : SessionsGetClientMetadataRequest::fromArray($params))->toArray();

        return $this->client->request('sessions.getClientMetadata', $paramsArray);
    }
}
