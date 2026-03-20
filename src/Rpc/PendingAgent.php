<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionAgentGetCurrentResult;
use Revolution\Copilot\Types\Rpc\SessionAgentListResult;
use Revolution\Copilot\Types\Rpc\SessionAgentReloadResult;
use Revolution\Copilot\Types\Rpc\SessionAgentSelectParams;
use Revolution\Copilot\Types\Rpc\SessionAgentSelectResult;

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
    public function list(): SessionAgentListResult
    {
        return SessionAgentListResult::fromArray(
            $this->client->request('session.agent.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Get the current agent.
     */
    public function getCurrent(): SessionAgentGetCurrentResult
    {
        return SessionAgentGetCurrentResult::fromArray(
            $this->client->request('session.agent.getCurrent', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Select an agent.
     */
    public function select(SessionAgentSelectParams|array $params): SessionAgentSelectResult
    {
        $paramsArray = ($params instanceof SessionAgentSelectParams ? $params : SessionAgentSelectParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionAgentSelectResult::fromArray(
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
    public function reload(): SessionAgentReloadResult
    {
        return SessionAgentReloadResult::fromArray(
            $this->client->request('session.agent.reload', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
