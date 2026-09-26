<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Pending workflow RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 *
 * Workflow calls use the same JSON-compatible request and response payloads as
 * the CLI protocol. The high-level extension workflow authoring API is not
 * exposed by this low-level RPC wrapper.
 */
class PendingWorkflow
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function run(array $params): array
    {
        return $this->request('session.workflow.run', $params);
    }

    public function resume(array $params): array
    {
        return $this->request('session.workflow.resume', $params);
    }

    public function getRun(array $params): array
    {
        return $this->request('session.workflow.getRun', $params);
    }

    public function listRuns(array $params = []): array
    {
        return $this->request('session.workflow.listRuns', $params);
    }

    public function getRunDetail(array $params): array
    {
        return $this->request('session.workflow.getRunDetail', $params);
    }

    public function getRunProgress(array $params): array
    {
        return $this->request('session.workflow.getRunProgress', $params);
    }

    public function cancel(array $params): array
    {
        return $this->request('session.workflow.cancel', $params);
    }

    public function pause(array $params): array
    {
        return $this->request('session.workflow.pause', $params);
    }

    public function log(array $params): array
    {
        return $this->request('session.workflow.log', $params);
    }

    public function agent(array $params): array
    {
        return $this->request('session.workflow.agent', $params);
    }

    /**
     * Run a workflow in response to a workflow tool invocation.
     *
     * @internal
     */
    public function runFromTool(array $params): array
    {
        return $this->request('session.workflow.runFromTool', $params);
    }

    /**
     * Resume a workflow in response to a workflow tool invocation.
     *
     * @internal
     */
    public function resumeFromTool(array $params): array
    {
        return $this->request('session.workflow.resumeFromTool', $params);
    }

    /**
     * Acknowledge a workflow checkpoint reached during extension execution.
     *
     * @internal
     */
    public function pauseAtCheckpoint(array $params): array
    {
        return $this->request('session.workflow.pauseAtCheckpoint', $params);
    }

    public function journal(): PendingWorkflowJournal
    {
        return new PendingWorkflowJournal($this->client, $this->sessionId);
    }

    protected function request(string $method, array $params): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request($method, $params);
    }
}
