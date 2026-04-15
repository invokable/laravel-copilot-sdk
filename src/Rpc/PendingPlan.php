<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PlanReadResult;
use Revolution\Copilot\Types\Rpc\PlanUpdateRequest;

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
    public function read(): PlanReadResult
    {
        return PlanReadResult::fromArray(
            $this->client->request('session.plan.read', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Update the plan.
     */
    public function update(PlanUpdateRequest|array $params): array
    {
        $paramsArray = ($params instanceof PlanUpdateRequest ? $params : PlanUpdateRequest::fromArray($params))->toArray();
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
