<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\TaskExecutionMode;
use Revolution\Copilot\Enums\TaskShellAttachmentMode;
use Revolution\Copilot\Enums\TaskStatus;
use Revolution\Copilot\Types\Rpc\TaskAgentInfo;
use Revolution\Copilot\Types\Rpc\TaskList;
use Revolution\Copilot\Types\Rpc\TaskShellInfo;
use Revolution\Copilot\Types\Rpc\TasksCancelRequest;
use Revolution\Copilot\Types\Rpc\TasksCancelResult;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundRequest;
use Revolution\Copilot\Types\Rpc\TasksPromoteToBackgroundResult;
use Revolution\Copilot\Types\Rpc\TasksRemoveRequest;
use Revolution\Copilot\Types\Rpc\TasksRemoveResult;
use Revolution\Copilot\Types\Rpc\TasksStartAgentRequest;
use Revolution\Copilot\Types\Rpc\TasksStartAgentResult;

describe('TaskAgentInfo', function () {
    it('can be created from array with all fields', function () {
        $info = TaskAgentInfo::fromArray([
            'id' => 'task-123',
            'toolCallId' => 'tool-456',
            'description' => 'Explore task',
            'status' => 'running',
            'startedAt' => '2024-01-01T00:00:00Z',
            'agentType' => 'explore',
            'prompt' => 'Search the codebase',
            'completedAt' => '2024-01-01T00:01:00Z',
            'activeTimeMs' => 60000,
            'activeStartedAt' => '2024-01-01T00:00:10Z',
            'error' => null,
            'result' => 'Found 42 files',
            'model' => 'gpt-4',
            'executionMode' => 'background',
            'canPromoteToBackground' => true,
            'latestResponse' => 'Searching...',
            'idleSince' => '2024-01-01T00:00:50Z',
        ]);

        expect($info->id)->toBe('task-123')
            ->and($info->toolCallId)->toBe('tool-456')
            ->and($info->description)->toBe('Explore task')
            ->and($info->status)->toBe(TaskStatus::Running)
            ->and($info->startedAt)->toBe('2024-01-01T00:00:00Z')
            ->and($info->agentType)->toBe('explore')
            ->and($info->prompt)->toBe('Search the codebase')
            ->and($info->completedAt)->toBe('2024-01-01T00:01:00Z')
            ->and($info->activeTimeMs)->toBe(60000)
            ->and($info->result)->toBe('Found 42 files')
            ->and($info->model)->toBe('gpt-4')
            ->and($info->executionMode)->toBe(TaskExecutionMode::Background)
            ->and($info->canPromoteToBackground)->toBeTrue()
            ->and($info->latestResponse)->toBe('Searching...')
            ->and($info->idleSince)->toBe('2024-01-01T00:00:50Z');
    });

    it('can be created from minimal array', function () {
        $info = TaskAgentInfo::fromArray([]);

        expect($info->id)->toBe('')
            ->and($info->status)->toBe(TaskStatus::Running)
            ->and($info->completedAt)->toBeNull()
            ->and($info->executionMode)->toBeNull()
            ->and($info->canPromoteToBackground)->toBeNull();
    });

    it('converts to array with optional fields omitted when null', function () {
        $info = TaskAgentInfo::fromArray([
            'id' => 'abc',
            'toolCallId' => 'tc1',
            'description' => 'desc',
            'status' => 'idle',
            'startedAt' => '2024-01-01T00:00:00Z',
            'agentType' => 'task',
            'prompt' => 'do something',
        ]);

        $array = $info->toArray();

        expect($array['type'])->toBe('agent')
            ->and($array['id'])->toBe('abc')
            ->and($array['status'])->toBe('idle')
            ->and($array)->not->toHaveKey('completedAt')
            ->and($array)->not->toHaveKey('model')
            ->and($array)->not->toHaveKey('result');
    });

    it('implements Arrayable', function () {
        $info = TaskAgentInfo::fromArray(['id' => 'x', 'toolCallId' => 'y', 'description' => 'z', 'status' => 'completed', 'startedAt' => '', 'agentType' => 'task', 'prompt' => 'p']);
        expect($info)->toBeInstanceOf(Arrayable::class);
    });
});

describe('TaskShellInfo', function () {
    it('can be created from array with all fields', function () {
        $info = TaskShellInfo::fromArray([
            'id' => 'shell-1',
            'description' => 'Run tests',
            'status' => 'completed',
            'startedAt' => '2024-01-01T00:00:00Z',
            'command' => 'npm test',
            'attachmentMode' => 'detached',
            'completedAt' => '2024-01-01T00:02:00Z',
            'executionMode' => 'background',
            'canPromoteToBackground' => false,
            'logPath' => '/tmp/shell.log',
            'pid' => 1234,
        ]);

        expect($info->id)->toBe('shell-1')
            ->and($info->status)->toBe(TaskStatus::Completed)
            ->and($info->command)->toBe('npm test')
            ->and($info->attachmentMode)->toBe(TaskShellAttachmentMode::Detached)
            ->and($info->executionMode)->toBe(TaskExecutionMode::Background)
            ->and($info->logPath)->toBe('/tmp/shell.log')
            ->and($info->pid)->toBe(1234);
    });

    it('can be created from minimal array', function () {
        $info = TaskShellInfo::fromArray([]);

        expect($info->id)->toBe('')
            ->and($info->status)->toBe(TaskStatus::Running)
            ->and($info->attachmentMode)->toBe(TaskShellAttachmentMode::Attached)
            ->and($info->pid)->toBeNull();
    });

    it('converts to array with type=shell', function () {
        $info = TaskShellInfo::fromArray([
            'id' => 's1',
            'description' => 'd',
            'status' => 'running',
            'startedAt' => '',
            'command' => 'ls',
            'attachmentMode' => 'attached',
        ]);

        $array = $info->toArray();

        expect($array['type'])->toBe('shell')
            ->and($array['command'])->toBe('ls')
            ->and($array)->not->toHaveKey('pid');
    });
});

describe('TaskList', function () {
    it('can be created from empty array', function () {
        $list = TaskList::fromArray([]);
        expect($list->tasks)->toBe([]);
    });

    it('creates TaskAgentInfo for agent tasks', function () {
        $list = TaskList::fromArray([
            'tasks' => [
                [
                    'type' => 'agent',
                    'id' => 'a1',
                    'toolCallId' => 'tc1',
                    'description' => 'desc',
                    'status' => 'running',
                    'startedAt' => '',
                    'agentType' => 'explore',
                    'prompt' => 'p',
                ],
            ],
        ]);

        expect($list->tasks)->toHaveCount(1)
            ->and($list->tasks[0])->toBeInstanceOf(TaskAgentInfo::class);
    });

    it('creates TaskShellInfo for shell tasks', function () {
        $list = TaskList::fromArray([
            'tasks' => [
                [
                    'type' => 'shell',
                    'id' => 's1',
                    'description' => 'desc',
                    'status' => 'running',
                    'startedAt' => '',
                    'command' => 'echo hello',
                    'attachmentMode' => 'attached',
                ],
            ],
        ]);

        expect($list->tasks)->toHaveCount(1)
            ->and($list->tasks[0])->toBeInstanceOf(TaskShellInfo::class);
    });

    it('converts to array', function () {
        $list = TaskList::fromArray(['tasks' => []]);
        expect($list->toArray())->toBe(['tasks' => []]);
    });
});

describe('TasksStartAgentRequest', function () {
    it('can be created with required fields', function () {
        $req = new TasksStartAgentRequest(
            agentType: 'explore',
            prompt: 'find files',
            name: 'my-task',
        );

        expect($req->agentType)->toBe('explore')
            ->and($req->prompt)->toBe('find files')
            ->and($req->name)->toBe('my-task')
            ->and($req->description)->toBeNull()
            ->and($req->model)->toBeNull();
    });

    it('converts to array omitting null optional fields', function () {
        $req = new TasksStartAgentRequest(agentType: 'task', prompt: 'do it', name: 'task-1');
        $array = $req->toArray();

        expect($array)->toHaveKeys(['agentType', 'prompt', 'name'])
            ->and($array)->not->toHaveKey('description')
            ->and($array)->not->toHaveKey('model');
    });

    it('includes optional fields when set', function () {
        $req = new TasksStartAgentRequest(
            agentType: 'general-purpose',
            prompt: 'complex task',
            name: 'gp-task',
            description: 'A complex task',
            model: 'gpt-4',
        );
        $array = $req->toArray();

        expect($array['description'])->toBe('A complex task')
            ->and($array['model'])->toBe('gpt-4');
    });
});

describe('TasksStartAgentResult', function () {
    it('can be created from array', function () {
        $result = TasksStartAgentResult::fromArray(['agentId' => 'agent-abc']);
        expect($result->agentId)->toBe('agent-abc');
    });

    it('converts to array', function () {
        $result = new TasksStartAgentResult(agentId: 'abc');
        expect($result->toArray())->toBe(['agentId' => 'abc']);
    });
});

describe('TasksCancelRequest', function () {
    it('can be created and converted', function () {
        $req = new TasksCancelRequest(id: 'task-1');
        expect($req->id)->toBe('task-1')
            ->and($req->toArray())->toBe(['id' => 'task-1']);
    });
});

describe('TasksCancelResult', function () {
    it('can be created from array', function () {
        $result = TasksCancelResult::fromArray(['cancelled' => true]);
        expect($result->cancelled)->toBeTrue();
    });

    it('defaults to false', function () {
        $result = TasksCancelResult::fromArray([]);
        expect($result->cancelled)->toBeFalse();
    });
});

describe('TasksPromoteToBackgroundRequest', function () {
    it('can be created and converted', function () {
        $req = new TasksPromoteToBackgroundRequest(id: 'task-2');
        expect($req->id)->toBe('task-2')
            ->and($req->toArray())->toBe(['id' => 'task-2']);
    });
});

describe('TasksPromoteToBackgroundResult', function () {
    it('can be created from array', function () {
        $result = TasksPromoteToBackgroundResult::fromArray(['promoted' => true]);
        expect($result->promoted)->toBeTrue();
    });
});

describe('TasksRemoveRequest', function () {
    it('can be created and converted', function () {
        $req = new TasksRemoveRequest(id: 'task-3');
        expect($req->id)->toBe('task-3')
            ->and($req->toArray())->toBe(['id' => 'task-3']);
    });
});

describe('TasksRemoveResult', function () {
    it('can be created from array', function () {
        $result = TasksRemoveResult::fromArray(['removed' => true]);
        expect($result->removed)->toBeTrue();
    });

    it('defaults to false', function () {
        $result = TasksRemoveResult::fromArray([]);
        expect($result->removed)->toBeFalse();
    });
});
