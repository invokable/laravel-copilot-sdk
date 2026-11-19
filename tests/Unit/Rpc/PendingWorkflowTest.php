<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingWorkflow;
use Revolution\Copilot\Rpc\PendingWorkflowJournal;

describe('PendingWorkflow', function () {
    it('forwards each workflow operation with the session id', function (string $operation, string $method) {
        $params = [
            'workflowId' => 'workflow-123',
            'sessionId' => 'caller-provided-session',
        ];
        $expectedParams = [
            'workflowId' => 'workflow-123',
            'sessionId' => 'session-workflow',
        ];
        $response = ['operation' => $operation];

        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with($method, $expectedParams)
            ->andReturn($response);

        $result = (new PendingWorkflow($client, 'session-workflow'))
            ->{$operation}($params);

        expect($result)->toBe($response);
    })->with([
        'run' => ['run', 'session.workflow.run'],
        'resume' => ['resume', 'session.workflow.resume'],
        'get run' => ['getRun', 'session.workflow.getRun'],
        'list runs' => ['listRuns', 'session.workflow.listRuns'],
        'get run detail' => ['getRunDetail', 'session.workflow.getRunDetail'],
        'get run progress' => ['getRunProgress', 'session.workflow.getRunProgress'],
        'cancel' => ['cancel', 'session.workflow.cancel'],
        'pause' => ['pause', 'session.workflow.pause'],
        'log' => ['log', 'session.workflow.log'],
        'agent' => ['agent', 'session.workflow.agent'],
        'run from tool' => ['runFromTool', 'session.workflow.runFromTool'],
        'resume from tool' => ['resumeFromTool', 'session.workflow.resumeFromTool'],
        'pause at checkpoint' => ['pauseAtCheckpoint', 'session.workflow.pauseAtCheckpoint'],
    ]);

    it('lists runs with empty params by default', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.workflow.listRuns', ['sessionId' => 'session-workflow'])
            ->andReturn([]);

        $result = (new PendingWorkflow($client, 'session-workflow'))->listRuns();

        expect($result)->toBe([]);
    });

    it('creates a journal scoped to the workflow session', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.workflow.journal.get', [
                'entryId' => 'entry-123',
                'sessionId' => 'session-workflow',
            ])
            ->andReturn(['value' => 'saved']);

        $journal = (new PendingWorkflow($client, 'session-workflow'))->journal();
        $result = $journal->get(['entryId' => 'entry-123']);

        expect($journal)->toBeInstanceOf(PendingWorkflowJournal::class)
            ->and($result)->toBe(['value' => 'saved']);
    });
});
