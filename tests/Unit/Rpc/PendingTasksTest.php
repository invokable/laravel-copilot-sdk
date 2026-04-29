<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingTasks;
use Revolution\Copilot\Types\Rpc\TaskAgentInfo;
use Revolution\Copilot\Types\Rpc\TaskList;
use Revolution\Copilot\Types\Rpc\TasksCancelRequest;
use Revolution\Copilot\Types\Rpc\TasksCancelResult;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundRequest;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundResult;
use Revolution\Copilot\Types\Rpc\TasksRemoveRequest;
use Revolution\Copilot\Types\Rpc\TasksRemoveResult;
use Revolution\Copilot\Types\Rpc\TasksStartAgentRequest;
use Revolution\Copilot\Types\Rpc\TasksStartAgentResult;

describe('PendingTasks', function () {
    it('calls session.tasks.startAgent and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tasks.startAgent',
                Mockery::on(fn ($params) => $params['sessionId'] === 'sess-1'
                    && $params['agentType'] === 'explore'
                    && $params['prompt'] === 'find files'
                    && $params['name'] === 'my-task'),
            )
            ->andReturn(['agentId' => 'agent-abc']);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->startAgent(new TasksStartAgentRequest(
            agentType: 'explore',
            prompt: 'find files',
            name: 'my-task',
        ));

        expect($result)->toBeInstanceOf(TasksStartAgentResult::class)
            ->and($result->agentId)->toBe('agent-abc');
    });

    it('calls session.tasks.startAgent with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.startAgent', Mockery::type('array'))
            ->andReturn(['agentId' => 'agent-xyz']);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->startAgent(['agentType' => 'task', 'prompt' => 'p', 'name' => 'n']);

        expect($result)->toBeInstanceOf(TasksStartAgentResult::class);
    });

    it('calls session.tasks.list and returns TaskList', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.list', ['sessionId' => 'sess-1'])
            ->andReturn([
                'tasks' => [
                    [
                        'type' => 'agent',
                        'id' => 'task-1',
                        'toolCallId' => 'tc1',
                        'description' => 'Explore',
                        'status' => 'running',
                        'startedAt' => '2024-01-01T00:00:00Z',
                        'agentType' => 'explore',
                        'prompt' => 'find',
                    ],
                ],
            ]);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(TaskList::class)
            ->and($result->tasks)->toHaveCount(1)
            ->and($result->tasks[0])->toBeInstanceOf(TaskAgentInfo::class);
    });

    it('calls session.tasks.promoteToBackground and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tasks.promoteToBackground',
                Mockery::on(fn ($params) => $params['sessionId'] === 'sess-1' && $params['id'] === 'task-1'),
            )
            ->andReturn(['promoted' => true]);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->promoteToBackground(new TasksPromoteToBackgroundRequest(id: 'task-1'));

        expect($result)->toBeInstanceOf(TasksPromoteToBackgroundResult::class)
            ->and($result->promoted)->toBeTrue();
    });

    it('calls session.tasks.cancel and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tasks.cancel',
                Mockery::on(fn ($params) => $params['sessionId'] === 'sess-1' && $params['id'] === 'task-1'),
            )
            ->andReturn(['cancelled' => true]);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->cancel(new TasksCancelRequest(id: 'task-1'));

        expect($result)->toBeInstanceOf(TasksCancelResult::class)
            ->and($result->cancelled)->toBeTrue();
    });

    it('calls session.tasks.remove and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tasks.remove',
                Mockery::on(fn ($params) => $params['sessionId'] === 'sess-1' && $params['id'] === 'task-1'),
            )
            ->andReturn(['removed' => true]);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->remove(new TasksRemoveRequest(id: 'task-1'));

        expect($result)->toBeInstanceOf(TasksRemoveResult::class)
            ->and($result->removed)->toBeTrue();
    });
});
