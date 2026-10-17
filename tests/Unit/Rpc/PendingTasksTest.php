<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\TaskClientActiveStatus;
use Revolution\Copilot\Enums\TaskClientUpdateKind;
use Revolution\Copilot\Enums\TaskStatus;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingTasks;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\TaskAgentInfo;
use Revolution\Copilot\Types\Rpc\TaskClientInfo;
use Revolution\Copilot\Types\Rpc\TaskClientProgress;
use Revolution\Copilot\Types\Rpc\TaskClientUpdate;
use Revolution\Copilot\Types\Rpc\TaskList;
use Revolution\Copilot\Types\Rpc\TasksCancelRequest;
use Revolution\Copilot\Types\Rpc\TasksCancelResult;
use Revolution\Copilot\Types\Rpc\TasksGetCurrentPromotableResult;
use Revolution\Copilot\Types\Rpc\TasksGetProgressResult;
use Revolution\Copilot\Types\Rpc\TaskShellOutputExit;
use Revolution\Copilot\Types\Rpc\TasksPromoteCurrentToBackgroundResult;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundRequest;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundResult;
use Revolution\Copilot\Types\Rpc\TasksReadOutputRequest;
use Revolution\Copilot\Types\Rpc\TasksReadOutputResult;
use Revolution\Copilot\Types\Rpc\TasksRefreshResult;
use Revolution\Copilot\Types\Rpc\TasksRegisterRequest;
use Revolution\Copilot\Types\Rpc\TasksRegisterResult;
use Revolution\Copilot\Types\Rpc\TasksRemoveRequest;
use Revolution\Copilot\Types\Rpc\TasksRemoveResult;
use Revolution\Copilot\Types\Rpc\TasksSendMessageRequest;
use Revolution\Copilot\Types\Rpc\TasksSendMessageResult;
use Revolution\Copilot\Types\Rpc\TasksStartAgentRequest;
use Revolution\Copilot\Types\Rpc\TasksStartAgentResult;
use Revolution\Copilot\Types\Rpc\TasksUpdateResult;
use Revolution\Copilot\Types\Rpc\TasksWaitForPendingResult;

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

    it('registers a client-owned task with the session ID', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tasks.register',
                Mockery::on(fn ($params) => $params === [
                    'type' => 'client',
                    'clientTaskId' => 'job-1',
                    'description' => 'Process records',
                    'cancellable' => true,
                    'displayName' => 'Import',
                    'expectedSequence' => 2,
                    'sessionId' => 'sess-1',
                ]),
            )
            ->andReturn([
                'task' => clientTaskPayloadForRpcTest(),
                'created' => true,
                'reclaimed' => false,
            ]);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->register(new TasksRegisterRequest(
            clientTaskId: 'job-1',
            description: 'Process records',
            cancellable: true,
            displayName: 'Import',
            expectedSequence: 2,
        ));

        expect($result)->toBeInstanceOf(TasksRegisterResult::class)
            ->and($result->task)->toBeInstanceOf(TaskClientInfo::class)
            ->and($result->created)->toBeTrue()
            ->and($result->reclaimed)->toBeFalse();
    });

    it('publishes a client task update with its sequence', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tasks.update',
                Mockery::on(fn ($params) => $params === [
                    'id' => 'client-1',
                    'sequence' => 3,
                    'update' => [
                        'kind' => 'progress',
                        'status' => 'running',
                        'phase' => 'processing',
                    ],
                    'sessionId' => 'sess-1',
                ]),
            )
            ->andReturn([
                'task' => clientTaskPayloadForRpcTest(),
                'applied' => true,
                'duplicate' => false,
            ]);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->update([
            'id' => 'client-1',
            'sequence' => 3,
            'update' => new TaskClientUpdate(
                kind: TaskClientUpdateKind::Progress,
                status: TaskClientActiveStatus::Running,
                phase: 'processing',
            ),
        ]);

        expect($result)->toBeInstanceOf(TasksUpdateResult::class)
            ->and($result->task)->toBeInstanceOf(TaskClientInfo::class)
            ->and($result->applied)->toBeTrue()
            ->and($result->duplicate)->toBeFalse();
    });

    it('returns typed progress for a client task', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.getProgress', [
                'id' => 'client-1',
                'sessionId' => 'sess-1',
            ])
            ->andReturn([
                'progress' => [
                    'type' => 'client',
                    'status' => 'running',
                    'sequence' => 3,
                    'updatedAt' => '2026-01-01T00:00:00Z',
                    'recentActivity' => [],
                ],
            ]);

        $result = (new PendingTasks($client, 'sess-1'))->getProgress(['id' => 'client-1']);

        expect($result)->toBeInstanceOf(TasksGetProgressResult::class)
            ->and($result->progress)->toBeInstanceOf(TaskClientProgress::class);
    });

    it('reads incremental shell output and decodes exit information', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.readOutput', [
                'id' => 'shell-1',
                'cursor' => 'cursor-1',
                'waitMs' => 1000,
                'sessionId' => 'sess-1',
            ])
            ->andReturn([
                'text' => "first line\n",
                'cursor' => 'cursor-2',
                'exit' => [
                    'status' => 'completed',
                    'exitCode' => 0,
                ],
            ]);

        $result = (new SessionRpc($client, 'sess-1'))->tasks()->readOutput(new TasksReadOutputRequest(
            id: 'shell-1',
            cursor: 'cursor-1',
            waitMs: 1000,
        ));

        expect($result)->toBeInstanceOf(TasksReadOutputResult::class)
            ->and($result->text)->toBe("first line\n")
            ->and($result->cursor)->toBe('cursor-2')
            ->and($result->exit)->toBeInstanceOf(TaskShellOutputExit::class)
            ->and($result->exit->status)->toBe(TaskStatus::Completed)
            ->and($result->exit->exitCode)->toBe(0)
            ->and($result->toArray())->toBe([
                'text' => "first line\n",
                'cursor' => 'cursor-2',
                'exit' => [
                    'status' => 'completed',
                    'exitCode' => 0,
                ],
            ]);
    });

    it('reads shell output from array params without optional fields or exit information', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.readOutput', [
                'id' => 'shell-1',
                'sessionId' => 'sess-1',
            ])
            ->andReturn([
                'text' => 'still running',
                'cursor' => 'cursor-1',
            ]);

        $result = (new PendingTasks($client, 'sess-1'))->readOutput(['id' => 'shell-1']);

        expect($result)->toBeInstanceOf(TasksReadOutputResult::class)
            ->and($result->text)->toBe('still running')
            ->and($result->cursor)->toBe('cursor-1')
            ->and($result->exit)->toBeNull()
            ->and($result->toArray())->toBe([
                'text' => 'still running',
                'cursor' => 'cursor-1',
            ]);
    });

    it('refreshes task metadata', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.refresh', ['sessionId' => 'sess-1'])
            ->andReturn([]);

        $result = (new PendingTasks($client, 'sess-1'))->refresh();

        expect($result)->toBeInstanceOf(TasksRefreshResult::class)
            ->and($result->toArray())->toBe([]);
    });

    it('waits for pending tasks', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.waitForPending', ['sessionId' => 'sess-1'])
            ->andReturn([]);

        $result = (new PendingTasks($client, 'sess-1'))->waitForPending();

        expect($result)->toBeInstanceOf(TasksWaitForPendingResult::class)
            ->and($result->toArray())->toBe([]);
    });

    it('returns the current promotable task as a client task', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.getCurrentPromotable', ['sessionId' => 'sess-1'])
            ->andReturn(['task' => clientTaskPayloadForRpcTest()]);

        $result = (new PendingTasks($client, 'sess-1'))->getCurrentPromotable();

        expect($result)->toBeInstanceOf(TasksGetCurrentPromotableResult::class)
            ->and($result->task)->toBeInstanceOf(TaskClientInfo::class);
    });

    it('promotes the current task to background mode', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tasks.promoteCurrentToBackground', ['sessionId' => 'sess-1'])
            ->andReturn(['task' => clientTaskPayloadForRpcTest()]);

        $result = (new PendingTasks($client, 'sess-1'))->promoteCurrentToBackground();

        expect($result)->toBeInstanceOf(TasksPromoteCurrentToBackgroundResult::class)
            ->and($result->task)->toBeInstanceOf(TaskClientInfo::class);
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

    it('calls session.tasks.sendMessage and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tasks.sendMessage',
                Mockery::on(fn ($params) => $params['sessionId'] === 'sess-1'
                    && $params['id'] === 'task-1'
                    && $params['message'] === 'hello agent'),
            )
            ->andReturn(['sent' => true]);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->sendMessage(new TasksSendMessageRequest(id: 'task-1', message: 'hello agent'));

        expect($result)->toBeInstanceOf(TasksSendMessageResult::class)
            ->and($result->sent)->toBeTrue();
    });

    it('calls session.tasks.sendMessage with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tasks.sendMessage',
                Mockery::on(fn ($params) => $params['sessionId'] === 'sess-1'
                    && $params['id'] === 'task-2'
                    && $params['message'] === 'steer this'),
            )
            ->andReturn(['sent' => false, 'error' => 'Task not found']);

        $pending = new PendingTasks($client, 'sess-1');
        $result = $pending->sendMessage(['id' => 'task-2', 'message' => 'steer this']);

        expect($result)->toBeInstanceOf(TasksSendMessageResult::class)
            ->and($result->sent)->toBeFalse()
            ->and($result->error)->toBe('Task not found');
    });
});

function clientTaskPayloadForRpcTest(): array
{
    return [
        'type' => 'client',
        'id' => 'client-1',
        'clientTaskId' => 'job-1',
        'description' => 'Process records',
        'status' => 'running',
        'owner' => [
            'participantId' => 'participant-1',
            'joinId' => 'join-1',
            'kind' => 'sdk',
            'presence' => 'connected',
        ],
        'startedAt' => '2026-01-01T00:00:00Z',
        'updatedAt' => '2026-01-01T00:00:00Z',
        'activeTimeMs' => 0,
        'executionMode' => 'background',
        'canCancel' => true,
        'sequence' => 1,
    ];
}
