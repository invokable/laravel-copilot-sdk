<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Durable journal operations for workflow execution.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingWorkflowJournal
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function get(array $params): array
    {
        return $this->request('session.workflow.journal.get', $params);
    }

    public function put(array $params): array
    {
        return $this->request('session.workflow.journal.put', $params);
    }

    protected function request(string $method, array $params): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request($method, $params);
    }
}
