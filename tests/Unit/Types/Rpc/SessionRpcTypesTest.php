<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\LogLevel;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\FleetStartRequest;
use Revolution\Copilot\Types\Rpc\FleetStartResult;
use Revolution\Copilot\Types\Rpc\HandleToolCallResult;
use Revolution\Copilot\Types\Rpc\HistoryCompactResult;
use Revolution\Copilot\Types\Rpc\HistoryTruncateRequest;
use Revolution\Copilot\Types\Rpc\HistoryTruncateResult;
use Revolution\Copilot\Types\Rpc\LogRequest;
use Revolution\Copilot\Types\Rpc\LogResult;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToResult;
use Revolution\Copilot\Types\Rpc\ModeSetRequest;
use Revolution\Copilot\Types\Rpc\PermissionDecisionRequest;
use Revolution\Copilot\Types\Rpc\PermissionRequestResult;
use Revolution\Copilot\Types\Rpc\PlanReadResult;
use Revolution\Copilot\Types\Rpc\PlanUpdateRequest;
use Revolution\Copilot\Types\Rpc\ShellExecRequest;
use Revolution\Copilot\Types\Rpc\ShellExecResult;
use Revolution\Copilot\Types\Rpc\ShellKillRequest;
use Revolution\Copilot\Types\Rpc\ShellKillResult;
use Revolution\Copilot\Types\Rpc\ToolsHandlePendingToolCallRequest;

describe('CurrentModel', function () {
    it('can be created from array', function () {
        $result = CurrentModel::fromArray(['modelId' => 'gpt-4']);
        expect($result->modelId)->toBe('gpt-4');
    });

    it('can be created with null modelId', function () {
        $result = CurrentModel::fromArray([]);
        expect($result->modelId)->toBeNull();
    });
});

describe('ModelSwitchToRequest', function () {
    it('can be created and converted', function () {
        $params = new ModelSwitchToRequest(modelId: 'gpt-4');
        expect($params->toArray())->toBe(['modelId' => 'gpt-4']);
    });

    it('can be created with reasoningEffort', function () {
        $params = new ModelSwitchToRequest(
            modelId: 'claude-opus-4',
            reasoningEffort: ReasoningEffort::HIGH,
        );
        expect($params->toArray())->toBe([
            'modelId' => 'claude-opus-4',
            'reasoningEffort' => 'high',
        ]);
    });

    it('filters null reasoningEffort', function () {
        $params = new ModelSwitchToRequest(modelId: 'gpt-4', reasoningEffort: null);
        expect($params->toArray())->toBe(['modelId' => 'gpt-4']);
    });

    it('can be created from array with reasoningEffort', function () {
        $params = ModelSwitchToRequest::fromArray([
            'modelId' => 'o1-preview',
            'reasoningEffort' => 'medium',
        ]);
        expect($params->modelId)->toBe('o1-preview')
            ->and($params->reasoningEffort)->toBe('medium');
    });

    it('can be created from array without reasoningEffort', function () {
        $params = ModelSwitchToRequest::fromArray([
            'modelId' => 'gpt-4',
        ]);
        expect($params->modelId)->toBe('gpt-4')
            ->and($params->reasoningEffort)->toBeNull();
    });
});

describe('ModelSwitchToResult', function () {
    it('can be created from array', function () {
        $result = ModelSwitchToResult::fromArray(['modelId' => 'gpt-4']);
        expect($result->modelId)->toBe('gpt-4');
    });
});

describe('ModeSetRequest', function () {
    it('can be created and converted', function () {
        $params = new ModeSetRequest(mode: 'autopilot');
        expect($params->toArray())->toBe(['mode' => 'autopilot']);
    });
});

describe('PlanReadResult', function () {
    it('can be created from array with content', function () {
        $result = PlanReadResult::fromArray([
            'exists' => true,
            'content' => '# Plan',
        ]);

        expect($result->exists)->toBeTrue()
            ->and($result->content)->toBe('# Plan')
            ->and($result->path)->toBeNull();
    });

    it('can be created from array without content', function () {
        $result = PlanReadResult::fromArray(['exists' => false]);

        expect($result->exists)->toBeFalse()
            ->and($result->content)->toBeNull()
            ->and($result->path)->toBeNull();
    });

    it('can be created from array with path', function () {
        $result = PlanReadResult::fromArray([
            'exists' => true,
            'content' => '# Plan',
            'path' => '/workspace/plan.md',
        ]);

        expect($result->exists)->toBeTrue()
            ->and($result->path)->toBe('/workspace/plan.md');
    });

    it('includes path in toArray', function () {
        $result = new PlanReadResult(exists: true, content: '# Plan', path: '/workspace/plan.md');

        expect($result->toArray())->toBe([
            'exists' => true,
            'content' => '# Plan',
            'path' => '/workspace/plan.md',
        ]);
    });
});

describe('PlanUpdateRequest', function () {
    it('can be created and converted', function () {
        $params = new PlanUpdateRequest(content: '# Updated Plan');
        expect($params->toArray())->toBe(['content' => '# Updated Plan']);
    });
});

describe('FleetStartRequest', function () {
    it('can be created with prompt', function () {
        $params = new FleetStartRequest(prompt: 'build it');
        expect($params->toArray())->toBe(['prompt' => 'build it']);
    });

    it('filters null prompt', function () {
        $params = new FleetStartRequest;
        expect($params->toArray())->toBe([]);
    });
});

describe('FleetStartResult', function () {
    it('can be created from array', function () {
        $result = FleetStartResult::fromArray(['started' => true]);
        expect($result->started)->toBeTrue();
    });
});

describe('HistoryCompactResult', function () {
    it('can be created from array', function () {
        $result = HistoryCompactResult::fromArray([
            'success' => true,
            'tokensRemoved' => 1000,
            'messagesRemoved' => 5,
        ]);

        expect($result->success)->toBeTrue()
            ->and($result->tokensRemoved)->toBe(1000)
            ->and($result->messagesRemoved)->toBe(5);
    });

    it('can convert to array', function () {
        $result = new HistoryCompactResult(
            success: true,
            tokensRemoved: 500,
            messagesRemoved: 3,
        );

        expect($result->toArray())->toBe([
            'success' => true,
            'tokensRemoved' => 500,
            'messagesRemoved' => 3,
        ]);
    });
});

describe('HandleToolCallResult', function () {
    it('can be created from array', function () {
        $result = HandleToolCallResult::fromArray(['success' => true]);
        expect($result->success)->toBeTrue();
    });

    it('can convert to array', function () {
        $result = new HandleToolCallResult(success: false);
        expect($result->toArray())->toBe(['success' => false]);
    });
});

describe('ToolsHandlePendingToolCallRequest', function () {
    it('can be created with string result', function () {
        $params = new ToolsHandlePendingToolCallRequest(requestId: 'req-1', result: 'done');
        expect($params->toArray())->toBe(['requestId' => 'req-1', 'result' => 'done']);
    });

    it('can be created with structured result', function () {
        $params = new ToolsHandlePendingToolCallRequest(
            requestId: 'req-1',
            result: ['textResultForLlm' => 'output', 'resultType' => 'text'],
        );
        expect($params->toArray())->toMatchArray(['requestId' => 'req-1']);
    });

    it('can be created with error', function () {
        $params = new ToolsHandlePendingToolCallRequest(requestId: 'req-1', error: 'failed');
        expect($params->toArray())->toBe(['requestId' => 'req-1', 'error' => 'failed']);
    });

    it('omits null fields', function () {
        $params = new ToolsHandlePendingToolCallRequest(requestId: 'req-1');
        expect($params->toArray())->toBe(['requestId' => 'req-1']);
    });

    it('can be created from array', function () {
        $params = ToolsHandlePendingToolCallRequest::fromArray([
            'requestId' => 'req-2',
            'result' => 'success',
        ]);
        expect($params->requestId)->toBe('req-2')
            ->and($params->result)->toBe('success');
    });
});

describe('PermissionRequestResult', function () {
    it('can be created from array', function () {
        $result = PermissionRequestResult::fromArray(['success' => true]);
        expect($result->success)->toBeTrue();
    });

    it('can convert to array', function () {
        $result = new PermissionRequestResult(success: true);
        expect($result->toArray())->toBe(['success' => true]);
    });
});

describe('PermissionDecisionRequest', function () {
    it('can be created with approved result', function () {
        $params = new PermissionDecisionRequest(
            requestId: 'perm-1',
            result: ['kind' => 'approved'],
        );
        expect($params->toArray())->toBe([
            'requestId' => 'perm-1',
            'result' => ['kind' => 'approved'],
        ]);
    });

    it('can be created from array', function () {
        $params = PermissionDecisionRequest::fromArray([
            'requestId' => 'perm-2',
            'result' => ['kind' => 'denied-interactively-by-user'],
        ]);
        expect($params->requestId)->toBe('perm-2')
            ->and($params->result['kind'])->toBe('denied-interactively-by-user');
    });
});

describe('LogRequest', function () {
    it('can be created with message only', function () {
        $params = new LogRequest(message: 'Processing started');
        expect($params->message)->toBe('Processing started')
            ->and($params->level)->toBeNull()
            ->and($params->ephemeral)->toBeNull();
    });

    it('can be created with all parameters', function () {
        $params = new LogRequest(
            message: 'Disk usage high',
            level: LogLevel::WARNING,
            ephemeral: true,
        );
        expect($params->message)->toBe('Disk usage high')
            ->and($params->level)->toBe(LogLevel::WARNING)
            ->and($params->ephemeral)->toBeTrue();
    });

    it('converts to array filtering null values', function () {
        $params = new LogRequest(message: 'Hello');
        expect($params->toArray())->toBe(['message' => 'Hello']);
    });

    it('converts to array with all values', function () {
        $params = new LogRequest(
            message: 'Error occurred',
            level: LogLevel::ERROR,
            ephemeral: false,
        );
        expect($params->toArray())->toBe([
            'message' => 'Error occurred',
            'level' => 'error',
            'ephemeral' => false,
        ]);
    });

    it('can be created from array', function () {
        $params = LogRequest::fromArray([
            'message' => 'Test message',
            'level' => 'info',
            'ephemeral' => true,
        ]);
        expect($params->message)->toBe('Test message')
            ->and($params->level)->toBe(LogLevel::INFO)
            ->and($params->ephemeral)->toBeTrue();
    });

    it('can be created from array with missing optional fields', function () {
        $params = LogRequest::fromArray([
            'message' => 'Just a message',
        ]);
        expect($params->message)->toBe('Just a message')
            ->and($params->level)->toBeNull()
            ->and($params->ephemeral)->toBeNull();
    });

    it('can be created with url', function () {
        $params = new LogRequest(
            message: 'See details',
            url: 'https://example.com/details',
        );
        expect($params->url)->toBe('https://example.com/details');
    });

    it('converts to array with url', function () {
        $params = new LogRequest(
            message: 'Check logs',
            level: LogLevel::INFO,
            url: 'https://example.com/logs',
        );
        expect($params->toArray())->toBe([
            'message' => 'Check logs',
            'level' => 'info',
            'url' => 'https://example.com/logs',
        ]);
    });

    it('can be created from array with url', function () {
        $params = LogRequest::fromArray([
            'message' => 'Details here',
            'url' => 'https://example.com/info',
        ]);
        expect($params->url)->toBe('https://example.com/info');
    });

    it('filters null url in toArray', function () {
        $params = new LogRequest(message: 'No URL');
        expect($params->toArray())->not->toHaveKey('url');
    });
});

describe('LogResult', function () {
    it('can be created with eventId', function () {
        $result = new LogResult(eventId: 'evt-123');
        expect($result->eventId)->toBe('evt-123');
    });

    it('can be created from array', function () {
        $result = LogResult::fromArray(['eventId' => 'evt-456']);
        expect($result->eventId)->toBe('evt-456');
    });

    it('can convert to array', function () {
        $result = new LogResult(eventId: 'evt-789');
        expect($result->toArray())->toBe(['eventId' => 'evt-789']);
    });
});

describe('ShellExecRequest', function () {
    it('can be created with required command only', function () {
        $params = new ShellExecRequest(command: 'ls -la');

        expect($params->command)->toBe('ls -la')
            ->and($params->cwd)->toBeNull()
            ->and($params->timeout)->toBeNull();
    });

    it('can be created with all fields', function () {
        $params = new ShellExecRequest(command: 'npm test', cwd: '/app', timeout: 60000);

        expect($params->command)->toBe('npm test')
            ->and($params->cwd)->toBe('/app')
            ->and($params->timeout)->toBe(60000);
    });

    it('can be created from array', function () {
        $params = ShellExecRequest::fromArray([
            'command' => 'echo hello',
            'cwd' => '/home/user',
            'timeout' => 5000,
        ]);

        expect($params->command)->toBe('echo hello')
            ->and($params->cwd)->toBe('/home/user')
            ->and($params->timeout)->toBe(5000);
    });

    it('can be created from array with command only', function () {
        $params = ShellExecRequest::fromArray(['command' => 'pwd']);

        expect($params->command)->toBe('pwd')
            ->and($params->cwd)->toBeNull()
            ->and($params->timeout)->toBeNull();
    });

    it('filters null values in toArray', function () {
        $params = new ShellExecRequest(command: 'ls');

        expect($params->toArray())->toBe(['command' => 'ls']);
    });

    it('includes all fields in toArray', function () {
        $params = new ShellExecRequest(command: 'npm test', cwd: '/app', timeout: 30000);

        expect($params->toArray())->toBe([
            'command' => 'npm test',
            'cwd' => '/app',
            'timeout' => 30000,
        ]);
    });
});

describe('ShellExecResult', function () {
    it('can be created with processId', function () {
        $result = new ShellExecResult(processId: 'proc-123');

        expect($result->processId)->toBe('proc-123');
    });

    it('can be created from array', function () {
        $result = ShellExecResult::fromArray(['processId' => 'proc-456']);

        expect($result->processId)->toBe('proc-456');
    });

    it('can convert to array', function () {
        $result = new ShellExecResult(processId: 'proc-789');

        expect($result->toArray())->toBe(['processId' => 'proc-789']);
    });
});

describe('ShellKillRequest', function () {
    it('can be created with processId only', function () {
        $params = new ShellKillRequest(processId: 'proc-123');

        expect($params->processId)->toBe('proc-123')
            ->and($params->signal)->toBeNull();
    });

    it('can be created with signal', function () {
        $params = new ShellKillRequest(processId: 'proc-123', signal: 'SIGKILL');

        expect($params->processId)->toBe('proc-123')
            ->and($params->signal)->toBe('SIGKILL');
    });

    it('can be created from array', function () {
        $params = ShellKillRequest::fromArray([
            'processId' => 'proc-456',
            'signal' => 'SIGTERM',
        ]);

        expect($params->processId)->toBe('proc-456')
            ->and($params->signal)->toBe('SIGTERM');
    });

    it('can be created from array without signal', function () {
        $params = ShellKillRequest::fromArray(['processId' => 'proc-789']);

        expect($params->processId)->toBe('proc-789')
            ->and($params->signal)->toBeNull();
    });

    it('filters null signal in toArray', function () {
        $params = new ShellKillRequest(processId: 'proc-123');

        expect($params->toArray())->toBe(['processId' => 'proc-123']);
    });

    it('includes signal in toArray when set', function () {
        $params = new ShellKillRequest(processId: 'proc-123', signal: 'SIGKILL');

        expect($params->toArray())->toBe([
            'processId' => 'proc-123',
            'signal' => 'SIGKILL',
        ]);
    });
});

describe('ShellKillResult', function () {
    it('can be created with killed true', function () {
        $result = new ShellKillResult(killed: true);

        expect($result->killed)->toBeTrue();
    });

    it('can be created with killed false', function () {
        $result = new ShellKillResult(killed: false);

        expect($result->killed)->toBeFalse();
    });

    it('can be created from array', function () {
        $result = ShellKillResult::fromArray(['killed' => true]);

        expect($result->killed)->toBeTrue();
    });

    it('casts killed to bool from truthy value', function () {
        $result = ShellKillResult::fromArray(['killed' => 1]);

        expect($result->killed)->toBeTrue();
    });

    it('can convert to array', function () {
        $result = new ShellKillResult(killed: true);

        expect($result->toArray())->toBe(['killed' => true]);
    });
});

describe('HistoryTruncateResult', function () {
    it('can be created from array', function () {
        $result = HistoryTruncateResult::fromArray([
            'eventsRemoved' => 7,
        ]);

        expect($result->eventsRemoved)->toBe(7);
    });

    it('can convert to array', function () {
        $result = new HistoryTruncateResult(eventsRemoved: 3);

        expect($result->toArray())->toBe([
            'eventsRemoved' => 3,
        ]);
    });
});

describe('HistoryTruncateRequest', function () {
    it('can be created from array', function () {
        $params = HistoryTruncateRequest::fromArray([
            'eventId' => 'evt-123',
        ]);

        expect($params->eventId)->toBe('evt-123');
    });

    it('can convert to array', function () {
        $params = new HistoryTruncateRequest(eventId: 'evt-456');

        expect($params->toArray())->toBe([
            'eventId' => 'evt-456',
        ]);
    });
});
