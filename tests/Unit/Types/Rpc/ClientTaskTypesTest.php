<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\TaskClientActiveStatus;
use Revolution\Copilot\Enums\TaskClientOwnerKind;
use Revolution\Copilot\Enums\TaskClientOwnerPresence;
use Revolution\Copilot\Enums\TaskClientStatus;
use Revolution\Copilot\Enums\TaskClientUpdateKind;
use Revolution\Copilot\Enums\TaskExecutionMode;
use Revolution\Copilot\Types\Rpc\TaskAgentInfo;
use Revolution\Copilot\Types\Rpc\TaskAgentProgress;
use Revolution\Copilot\Types\Rpc\TaskClientInfo;
use Revolution\Copilot\Types\Rpc\TaskClientOwner;
use Revolution\Copilot\Types\Rpc\TaskClientProgress;
use Revolution\Copilot\Types\Rpc\TaskClientUpdate;
use Revolution\Copilot\Types\Rpc\TaskInfo;
use Revolution\Copilot\Types\Rpc\TaskList;
use Revolution\Copilot\Types\Rpc\TaskProgress;
use Revolution\Copilot\Types\Rpc\TaskProgressLine;
use Revolution\Copilot\Types\Rpc\TaskShellInfo;
use Revolution\Copilot\Types\Rpc\TaskShellProgress;

describe('TaskInfo', function () {
    it('round-trips agent, shell, and client task info', function (array $payload, string $type) {
        $task = TaskInfo::fromArray($payload);

        expect($task)->toBeInstanceOf($type)
            ->and(TaskInfo::toArray($task))->toBe($payload);
    })->with([
        'agent' => [[
            'type' => 'agent',
            'id' => 'agent-1',
            'toolCallId' => 'tool-1',
            'description' => 'Explore',
            'status' => 'running',
            'startedAt' => '2026-01-01T00:00:00Z',
            'agentType' => 'explore',
            'prompt' => 'Find files',
        ], TaskAgentInfo::class],
        'shell' => [[
            'type' => 'shell',
            'id' => 'shell-1',
            'description' => 'Run tests',
            'status' => 'running',
            'startedAt' => '2026-01-01T00:00:00Z',
            'command' => 'composer test',
            'attachmentMode' => 'attached',
        ], TaskShellInfo::class],
        'client' => [[
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
            'updatedAt' => '2026-01-01T00:01:00Z',
            'activeTimeMs' => 60000,
            'executionMode' => 'background',
            'canCancel' => true,
            'sequence' => 1,
            'result' => null,
        ], TaskClientInfo::class],
    ]);

    it('includes client task info in task lists', function () {
        $list = TaskList::fromArray([
            'tasks' => [[
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
            ]],
        ]);

        expect($list->tasks[0])->toBeInstanceOf(TaskClientInfo::class);
    });
});

describe('TaskClientOwner', function () {
    it('converts owner metadata and optional fields', function () {
        $owner = TaskClientOwner::fromArray([
            'participantId' => 'participant-1',
            'joinId' => 'join-1',
            'kind' => 'extension',
            'displayName' => 'Queue worker',
            'source' => 'app',
            'presence' => 'disconnected',
            'disconnectedAt' => '2026-01-01T00:00:00Z',
        ]);

        expect($owner->kind)->toBe(TaskClientOwnerKind::Extension)
            ->and($owner->presence)->toBe(TaskClientOwnerPresence::Disconnected)
            ->and($owner->toArray())->toBe([
                'participantId' => 'participant-1',
                'joinId' => 'join-1',
                'kind' => 'extension',
                'displayName' => 'Queue worker',
                'source' => 'app',
                'presence' => 'disconnected',
                'disconnectedAt' => '2026-01-01T00:00:00Z',
            ]);
    });
});

describe('TaskClientUpdate', function () {
    it('round-trips each update kind', function (array $payload, TaskClientUpdateKind $kind) {
        $update = TaskClientUpdate::fromArray($payload);

        expect($update->kind)->toBe($kind)
            ->and($update->toArray())->toBe($payload);
    })->with([
        'progress' => [[
            'kind' => 'progress',
            'status' => 'idle',
            'message' => 'Waiting for input',
            'phase' => 'waiting',
            'percentage' => 42,
        ], TaskClientUpdateKind::Progress],
        'completed' => [[
            'kind' => 'completed',
            'message' => 'Finished',
            'result' => ['count' => 10],
        ], TaskClientUpdateKind::Completed],
        'failed' => [[
            'kind' => 'failed',
            'message' => 'Stopped',
            'error' => 'Worker failed',
            'code' => 'worker_error',
        ], TaskClientUpdateKind::Failed],
        'cancelled' => [[
            'kind' => 'cancelled',
            'message' => 'Cancelled',
            'reason' => 'Requested',
        ], TaskClientUpdateKind::Cancelled],
    ]);

    it('preserves explicit null progress values', function () {
        $update = TaskClientUpdate::fromArray([
            'kind' => 'progress',
            'phase' => null,
            'percentage' => null,
        ]);

        expect($update->status)->toBeNull()
            ->and($update->toArray())->toBe([
                'kind' => 'progress',
                'phase' => null,
                'percentage' => null,
            ]);
    });

    it('serializes typed progress statuses', function () {
        $update = new TaskClientUpdate(
            kind: TaskClientUpdateKind::Progress,
            status: TaskClientActiveStatus::Running,
        );

        expect($update->toArray())->toBe([
            'kind' => 'progress',
            'status' => 'running',
        ]);
    });
});

describe('TaskProgress', function () {
    it('converts task progress by type', function (array $payload, string $type) {
        $progress = TaskProgress::fromArray($payload);

        expect($progress)->toBeInstanceOf($type)
            ->and(TaskProgress::toArray($progress))->toBe($payload);
    })->with([
        'agent' => [[
            'type' => 'agent',
            'recentActivity' => [['message' => 'Reading', 'timestamp' => '2026-01-01T00:00:00Z']],
            'latestIntent' => 'Inspecting',
        ], TaskAgentProgress::class],
        'client' => [[
            'type' => 'client',
            'status' => 'running',
            'sequence' => 2,
            'updatedAt' => '2026-01-01T00:00:00Z',
            'phase' => 'working',
            'percentage' => 42,
            'lastMessage' => 'Halfway',
            'recentActivity' => [['message' => 'Started', 'timestamp' => '2026-01-01T00:00:00Z']],
        ], TaskClientProgress::class],
        'shell' => [[
            'type' => 'shell',
            'recentOutput' => 'Running',
            'pid' => 123,
        ], TaskShellProgress::class],
    ]);

    it('converts a null progress result', function () {
        expect(TaskProgress::fromArray(null))->toBeNull()
            ->and(TaskProgress::toArray(null))->toBeNull();
    });
});

describe('TaskClientInfo', function () {
    it('maps the client task lifecycle fields', function () {
        $task = TaskClientInfo::fromArray([
            'type' => 'client',
            'id' => 'client-1',
            'clientTaskId' => 'job-1',
            'description' => 'Process records',
            'status' => 'orphaned',
            'owner' => [
                'participantId' => 'participant-1',
                'joinId' => 'join-1',
                'kind' => 'sdk',
                'presence' => 'disconnected',
            ],
            'startedAt' => '2026-01-01T00:00:00Z',
            'updatedAt' => '2026-01-01T00:01:00Z',
            'activeTimeMs' => 60000,
            'executionMode' => 'background',
            'canCancel' => false,
            'sequence' => 3,
            'result' => ['count' => 5],
        ]);

        expect($task->status)->toBe(TaskClientStatus::Orphaned)
            ->and($task->executionMode)->toBe(TaskExecutionMode::Background)
            ->and($task->owner)->toBeInstanceOf(TaskClientOwner::class)
            ->and($task->result)->toBe(['count' => 5]);
    });

    it('maps client progress activity lines', function () {
        $progress = TaskClientProgress::fromArray([
            'status' => 'running',
            'sequence' => 2,
            'updatedAt' => '2026-01-01T00:00:00Z',
            'recentActivity' => [['message' => 'Started', 'timestamp' => '2026-01-01T00:00:00Z']],
        ]);

        expect($progress->recentActivity[0])->toBeInstanceOf(TaskProgressLine::class)
            ->and($progress->recentActivity[0]->message)->toBe('Started');
    });
});
