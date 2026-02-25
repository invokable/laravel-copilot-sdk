<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionPlanReadResult;
use Revolution\Copilot\Types\Rpc\SessionPlanUpdateParams;

/**
 * Pending plan RPC operations for a session.
 */
class PendingPlan
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Read the plan.
     */
    public function read(): SessionPlanReadResult
    {
        return SessionPlanReadResult::fromArray(
            $this->client->request('session.plan.read', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Update the plan.
     */
    public function update(SessionPlanUpdateParams|array $params): array
    {
        $paramsArray = $params instanceof SessionPlanUpdateParams ? $params->toArray() : $params;
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.plan.update', $paramsArray);
    }

    /**
     * Delete the plan.
     */
    public function delete(): array
    {
        return $this->client->request('session.plan.delete', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
