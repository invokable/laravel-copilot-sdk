<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AgentGetCurrentResult;
use Revolution\Copilot\Types\Rpc\AgentList;
use Revolution\Copilot\Types\Rpc\AgentReloadResult;
use Revolution\Copilot\Types\Rpc\AgentSelectRequest;
use Revolution\Copilot\Types\Rpc\AgentSelectResult;

/**
 * Pending agent RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingAgent
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List available agents.
     */
    public function list(): AgentList
    {
        return AgentList::fromArray(
            $this->client->request('session.agent.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Get the current agent.
     */
    public function getCurrent(): AgentGetCurrentResult
    {
        return AgentGetCurrentResult::fromArray(
            $this->client->request('session.agent.getCurrent', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Select an agent.
     */
    public function select(AgentSelectRequest|array $params): AgentSelectResult
    {
        $paramsArray = ($params instanceof AgentSelectRequest ? $params : AgentSelectRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return AgentSelectResult::fromArray(
            $this->client->request('session.agent.select', $paramsArray),
        );
    }

    /**
     * Deselect the current agent.
     */
    public function deselect(): array
    {
        return $this->client->request('session.agent.deselect', [
            'sessionId' => $this->sessionId,
        ]);
    }

    /**
     * Reload custom agents.
     */
    public function reload(): AgentReloadResult
    {
        return AgentReloadResult::fromArray(
            $this->client->request('session.agent.reload', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
