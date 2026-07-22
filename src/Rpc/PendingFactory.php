<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\FactoryAckResult;
use Revolution\Copilot\Types\Rpc\FactoryAgentRequest;
use Revolution\Copilot\Types\Rpc\FactoryAgentResult;
use Revolution\Copilot\Types\Rpc\FactoryCancelRequest;
use Revolution\Copilot\Types\Rpc\FactoryGetRunRequest;
use Revolution\Copilot\Types\Rpc\FactoryLogRequest;
use Revolution\Copilot\Types\Rpc\FactoryRunRequest;
use Revolution\Copilot\Types\Rpc\FactoryRunResult;

/**
 * Pending factory RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingFactory
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Run a registered factory by name at the top level.
     */
    public function run(FactoryRunRequest|array $params): FactoryRunResult
    {
        $paramsArray = ($params instanceof FactoryRunRequest ? $params : FactoryRunRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FactoryRunResult::fromArray(
            $this->client->request('session.factory.run', $paramsArray),
        );
    }

    /**
     * Get the current or settled envelope for a factory run.
     */
    public function getRun(FactoryGetRunRequest|array $params): FactoryRunResult
    {
        $paramsArray = ($params instanceof FactoryGetRunRequest ? $params : FactoryGetRunRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FactoryRunResult::fromArray(
            $this->client->request('session.factory.getRun', $paramsArray),
        );
    }

    /**
     * Request cancellation of a factory run and return its run envelope.
     */
    public function cancel(FactoryCancelRequest|array $params): FactoryRunResult
    {
        $paramsArray = ($params instanceof FactoryCancelRequest ? $params : FactoryCancelRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FactoryRunResult::fromArray(
            $this->client->request('session.factory.cancel', $paramsArray),
        );
    }

    /**
     * Record a batch of ordered factory progress lines.
     */
    public function log(FactoryLogRequest|array $params): FactoryAckResult
    {
        $paramsArray = ($params instanceof FactoryLogRequest ? $params : FactoryLogRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FactoryAckResult::fromArray(
            $this->client->request('session.factory.log', $paramsArray),
        );
    }

    /**
     * Run one factory-scoped subagent and return its result.
     */
    public function agent(FactoryAgentRequest|array $params): FactoryAgentResult
    {
        $paramsArray = ($params instanceof FactoryAgentRequest ? $params : FactoryAgentRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return FactoryAgentResult::fromArray(
            $this->client->request('session.factory.agent', $paramsArray),
        );
    }

    /**
     * Factory journal RPC operations.
     */
    public function journal(): PendingFactoryJournal
    {
        return new PendingFactoryJournal($this->client, $this->sessionId);
    }
}
