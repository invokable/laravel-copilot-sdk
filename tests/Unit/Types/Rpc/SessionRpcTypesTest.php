<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\LogLevel;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Types\Rpc\SessionFleetStartParams;
use Revolution\Copilot\Types\Rpc\SessionFleetStartResult;
use Revolution\Copilot\Types\Rpc\SessionHistoryCompactResult;
use Revolution\Copilot\Types\Rpc\SessionHistoryTruncateParams;
use Revolution\Copilot\Types\Rpc\SessionHistoryTruncateResult;
use Revolution\Copilot\Types\Rpc\SessionLogParams;
use Revolution\Copilot\Types\Rpc\SessionLogResult;
use Revolution\Copilot\Types\Rpc\SessionModeGetResult;
use Revolution\Copilot\Types\Rpc\SessionModelGetCurrentResult;
use Revolution\Copilot\Types\Rpc\SessionModelSwitchToParams;
use Revolution\Copilot\Types\Rpc\SessionModelSwitchToResult;
use Revolution\Copilot\Types\Rpc\SessionModeSetParams;
use Revolution\Copilot\Types\Rpc\SessionModeSetResult;
use Revolution\Copilot\Types\Rpc\SessionPermissionsHandlePendingPermissionRequestParams;
use Revolution\Copilot\Types\Rpc\SessionPermissionsHandlePendingPermissionRequestResult;
use Revolution\Copilot\Types\Rpc\SessionPlanReadResult;
use Revolution\Copilot\Types\Rpc\SessionPlanUpdateParams;
use Revolution\Copilot\Types\Rpc\SessionShellExecParams;
use Revolution\Copilot\Types\Rpc\SessionShellExecResult;
use Revolution\Copilot\Types\Rpc\SessionShellKillParams;
use Revolution\Copilot\Types\Rpc\SessionShellKillResult;
use Revolution\Copilot\Types\Rpc\SessionToolsHandlePendingToolCallParams;
use Revolution\Copilot\Types\Rpc\SessionToolsHandlePendingToolCallResult;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceCreateFileParams;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceListFilesResult;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceReadFileParams;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceReadFileResult;

describe('SessionModelGetCurrentResult', function () {
    it('can be created from array', function () {
        $result = SessionModelGetCurrentResult::fromArray(['modelId' => 'gpt-4']);
        expect($result->modelId)->toBe('gpt-4');
    });

    it('can be created with null modelId', function () {
        $result = SessionModelGetCurrentResult::fromArray([]);
        expect($result->modelId)->toBeNull();
    });
});

describe('SessionModelSwitchToParams', function () {
    it('can be created and converted', function () {
        $params = new SessionModelSwitchToParams(modelId: 'gpt-4');
        expect($params->toArray())->toBe(['modelId' => 'gpt-4']);
    });

    it('can be created with reasoningEffort', function () {
        $params = new SessionModelSwitchToParams(
            modelId: 'claude-opus-4',
            reasoningEffort: ReasoningEffort::HIGH,
        );
        expect($params->toArray())->toBe([
            'modelId' => 'claude-opus-4',
            'reasoningEffort' => 'high',
        ]);
    });

    it('filters null reasoningEffort', function () {
        $params = new SessionModelSwitchToParams(modelId: 'gpt-4', reasoningEffort: null);
        expect($params->toArray())->toBe(['modelId' => 'gpt-4']);
    });

    it('can be created from array with reasoningEffort', function () {
        $params = SessionModelSwitchToParams::fromArray([
            'modelId' => 'o1-preview',
            'reasoningEffort' => 'medium',
        ]);
        expect($params->modelId)->toBe('o1-preview')
            ->and($params->reasoningEffort)->toBe('medium');
    });

    it('can be created from array without reasoningEffort', function () {
        $params = SessionModelSwitchToParams::fromArray([
            'modelId' => 'gpt-4',
        ]);
        expect($params->modelId)->toBe('gpt-4')
            ->and($params->reasoningEffort)->toBeNull();
    });
});

describe('SessionModelSwitchToResult', function () {
    it('can be created from array', function () {
        $result = SessionModelSwitchToResult::fromArray(['modelId' => 'gpt-4']);
        expect($result->modelId)->toBe('gpt-4');
    });
});

describe('SessionModeGetResult', function () {
    it('can be created from array', function () {
        $result = SessionModeGetResult::fromArray(['mode' => 'interactive']);
        expect($result->mode)->toBe('interactive');
    });

    it('can convert to array', function () {
        $result = new SessionModeGetResult(mode: 'plan');
        expect($result->toArray())->toBe(['mode' => 'plan']);
    });
});

describe('SessionModeSetParams', function () {
    it('can be created and converted', function () {
        $params = new SessionModeSetParams(mode: 'autopilot');
        expect($params->toArray())->toBe(['mode' => 'autopilot']);
    });
});

describe('SessionModeSetResult', function () {
    it('can be created from array', function () {
        $result = SessionModeSetResult::fromArray(['mode' => 'plan']);
        expect($result->mode)->toBe('plan');
    });
});

describe('SessionPlanReadResult', function () {
    it('can be created from array with content', function () {
        $result = SessionPlanReadResult::fromArray([
            'exists' => true,
            'content' => '# Plan',
        ]);

        expect($result->exists)->toBeTrue()
            ->and($result->content)->toBe('# Plan')
            ->and($result->path)->toBeNull();
    });

    it('can be created from array without content', function () {
        $result = SessionPlanReadResult::fromArray(['exists' => false]);

        expect($result->exists)->toBeFalse()
            ->and($result->content)->toBeNull()
            ->and($result->path)->toBeNull();
    });

    it('can be created from array with path', function () {
        $result = SessionPlanReadResult::fromArray([
            'exists' => true,
            'content' => '# Plan',
            'path' => '/workspace/plan.md',
        ]);

        expect($result->exists)->toBeTrue()
            ->and($result->path)->toBe('/workspace/plan.md');
    });

    it('includes path in toArray', function () {
        $result = new SessionPlanReadResult(exists: true, content: '# Plan', path: '/workspace/plan.md');

        expect($result->toArray())->toBe([
            'exists' => true,
            'content' => '# Plan',
            'path' => '/workspace/plan.md',
        ]);
    });
});

describe('SessionPlanUpdateParams', function () {
    it('can be created and converted', function () {
        $params = new SessionPlanUpdateParams(content: '# Updated Plan');
        expect($params->toArray())->toBe(['content' => '# Updated Plan']);
    });
});

describe('SessionWorkspaceListFilesResult', function () {
    it('can be created from array', function () {
        $result = SessionWorkspaceListFilesResult::fromArray([
            'files' => ['file1.txt', 'file2.txt'],
        ]);

        expect($result->files)->toBe(['file1.txt', 'file2.txt']);
    });

    it('handles empty files list', function () {
        $result = SessionWorkspaceListFilesResult::fromArray([]);

        expect($result->files)->toBe([]);
    });
});

describe('SessionWorkspaceReadFileResult', function () {
    it('can be created from array', function () {
        $result = SessionWorkspaceReadFileResult::fromArray([
            'content' => 'file content',
        ]);

        expect($result->content)->toBe('file content');
    });
});

describe('SessionWorkspaceReadFileParams', function () {
    it('can be created and converted', function () {
        $params = new SessionWorkspaceReadFileParams(path: 'test.txt');
        expect($params->toArray())->toBe(['path' => 'test.txt']);
    });
});

describe('SessionWorkspaceCreateFileParams', function () {
    it('can be created and converted', function () {
        $params = new SessionWorkspaceCreateFileParams(path: 'test.txt', content: 'hello');
        expect($params->toArray())->toBe(['path' => 'test.txt', 'content' => 'hello']);
    });
});

describe('SessionFleetStartParams', function () {
    it('can be created with prompt', function () {
        $params = new SessionFleetStartParams(prompt: 'build it');
        expect($params->toArray())->toBe(['prompt' => 'build it']);
    });

    it('filters null prompt', function () {
        $params = new SessionFleetStartParams;
        expect($params->toArray())->toBe([]);
    });
});

describe('SessionFleetStartResult', function () {
    it('can be created from array', function () {
        $result = SessionFleetStartResult::fromArray(['started' => true]);
        expect($result->started)->toBeTrue();
    });
});

describe('SessionHistoryCompactResult', function () {
    it('can be created from array', function () {
        $result = SessionHistoryCompactResult::fromArray([
            'success' => true,
            'tokensRemoved' => 1000,
            'messagesRemoved' => 5,
        ]);

        expect($result->success)->toBeTrue()
            ->and($result->tokensRemoved)->toBe(1000)
            ->and($result->messagesRemoved)->toBe(5);
    });

    it('can convert to array', function () {
        $result = new SessionHistoryCompactResult(
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

describe('SessionToolsHandlePendingToolCallResult', function () {
    it('can be created from array', function () {
        $result = SessionToolsHandlePendingToolCallResult::fromArray(['success' => true]);
        expect($result->success)->toBeTrue();
    });

    it('can convert to array', function () {
        $result = new SessionToolsHandlePendingToolCallResult(success: false);
        expect($result->toArray())->toBe(['success' => false]);
    });
});

describe('SessionToolsHandlePendingToolCallParams', function () {
    it('can be created with string result', function () {
        $params = new SessionToolsHandlePendingToolCallParams(requestId: 'req-1', result: 'done');
        expect($params->toArray())->toBe(['requestId' => 'req-1', 'result' => 'done']);
    });

    it('can be created with structured result', function () {
        $params = new SessionToolsHandlePendingToolCallParams(
            requestId: 'req-1',
            result: ['textResultForLlm' => 'output', 'resultType' => 'text'],
        );
        expect($params->toArray())->toMatchArray(['requestId' => 'req-1']);
    });

    it('can be created with error', function () {
        $params = new SessionToolsHandlePendingToolCallParams(requestId: 'req-1', error: 'failed');
        expect($params->toArray())->toBe(['requestId' => 'req-1', 'error' => 'failed']);
    });

    it('omits null fields', function () {
        $params = new SessionToolsHandlePendingToolCallParams(requestId: 'req-1');
        expect($params->toArray())->toBe(['requestId' => 'req-1']);
    });

    it('can be created from array', function () {
        $params = SessionToolsHandlePendingToolCallParams::fromArray([
            'requestId' => 'req-2',
            'result' => 'success',
        ]);
        expect($params->requestId)->toBe('req-2')
            ->and($params->result)->toBe('success');
    });
});

describe('SessionPermissionsHandlePendingPermissionRequestResult', function () {
    it('can be created from array', function () {
        $result = SessionPermissionsHandlePendingPermissionRequestResult::fromArray(['success' => true]);
        expect($result->success)->toBeTrue();
    });

    it('can convert to array', function () {
        $result = new SessionPermissionsHandlePendingPermissionRequestResult(success: true);
        expect($result->toArray())->toBe(['success' => true]);
    });
});

describe('SessionPermissionsHandlePendingPermissionRequestParams', function () {
    it('can be created with approved result', function () {
        $params = new SessionPermissionsHandlePendingPermissionRequestParams(
            requestId: 'perm-1',
            result: ['kind' => 'approved'],
        );
        expect($params->toArray())->toBe([
            'requestId' => 'perm-1',
            'result' => ['kind' => 'approved'],
        ]);
    });

    it('can be created from array', function () {
        $params = SessionPermissionsHandlePendingPermissionRequestParams::fromArray([
            'requestId' => 'perm-2',
            'result' => ['kind' => 'denied-interactively-by-user'],
        ]);
        expect($params->requestId)->toBe('perm-2')
            ->and($params->result['kind'])->toBe('denied-interactively-by-user');
    });
});

describe('SessionLogParams', function () {
    it('can be created with message only', function () {
        $params = new SessionLogParams(message: 'Processing started');
        expect($params->message)->toBe('Processing started')
            ->and($params->level)->toBeNull()
            ->and($params->ephemeral)->toBeNull();
    });

    it('can be created with all parameters', function () {
        $params = new SessionLogParams(
            message: 'Disk usage high',
            level: LogLevel::WARNING,
            ephemeral: true,
        );
        expect($params->message)->toBe('Disk usage high')
            ->and($params->level)->toBe(LogLevel::WARNING)
            ->and($params->ephemeral)->toBeTrue();
    });

    it('converts to array filtering null values', function () {
        $params = new SessionLogParams(message: 'Hello');
        expect($params->toArray())->toBe(['message' => 'Hello']);
    });

    it('converts to array with all values', function () {
        $params = new SessionLogParams(
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
        $params = SessionLogParams::fromArray([
            'message' => 'Test message',
            'level' => 'info',
            'ephemeral' => true,
        ]);
        expect($params->message)->toBe('Test message')
            ->and($params->level)->toBe(LogLevel::INFO)
            ->and($params->ephemeral)->toBeTrue();
    });

    it('can be created from array with missing optional fields', function () {
        $params = SessionLogParams::fromArray([
            'message' => 'Just a message',
        ]);
        expect($params->message)->toBe('Just a message')
            ->and($params->level)->toBeNull()
            ->and($params->ephemeral)->toBeNull();
    });

    it('can be created with url', function () {
        $params = new SessionLogParams(
            message: 'See details',
            url: 'https://example.com/details',
        );
        expect($params->url)->toBe('https://example.com/details');
    });

    it('converts to array with url', function () {
        $params = new SessionLogParams(
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
        $params = SessionLogParams::fromArray([
            'message' => 'Details here',
            'url' => 'https://example.com/info',
        ]);
        expect($params->url)->toBe('https://example.com/info');
    });

    it('filters null url in toArray', function () {
        $params = new SessionLogParams(message: 'No URL');
        expect($params->toArray())->not->toHaveKey('url');
    });
});

describe('SessionLogResult', function () {
    it('can be created with eventId', function () {
        $result = new SessionLogResult(eventId: 'evt-123');
        expect($result->eventId)->toBe('evt-123');
    });

    it('can be created from array', function () {
        $result = SessionLogResult::fromArray(['eventId' => 'evt-456']);
        expect($result->eventId)->toBe('evt-456');
    });

    it('can convert to array', function () {
        $result = new SessionLogResult(eventId: 'evt-789');
        expect($result->toArray())->toBe(['eventId' => 'evt-789']);
    });
});

describe('SessionShellExecParams', function () {
    it('can be created with required command only', function () {
        $params = new SessionShellExecParams(command: 'ls -la');

        expect($params->command)->toBe('ls -la')
            ->and($params->cwd)->toBeNull()
            ->and($params->timeout)->toBeNull();
    });

    it('can be created with all fields', function () {
        $params = new SessionShellExecParams(command: 'npm test', cwd: '/app', timeout: 60000);

        expect($params->command)->toBe('npm test')
            ->and($params->cwd)->toBe('/app')
            ->and($params->timeout)->toBe(60000);
    });

    it('can be created from array', function () {
        $params = SessionShellExecParams::fromArray([
            'command' => 'echo hello',
            'cwd' => '/home/user',
            'timeout' => 5000,
        ]);

        expect($params->command)->toBe('echo hello')
            ->and($params->cwd)->toBe('/home/user')
            ->and($params->timeout)->toBe(5000);
    });

    it('can be created from array with command only', function () {
        $params = SessionShellExecParams::fromArray(['command' => 'pwd']);

        expect($params->command)->toBe('pwd')
            ->and($params->cwd)->toBeNull()
            ->and($params->timeout)->toBeNull();
    });

    it('filters null values in toArray', function () {
        $params = new SessionShellExecParams(command: 'ls');

        expect($params->toArray())->toBe(['command' => 'ls']);
    });

    it('includes all fields in toArray', function () {
        $params = new SessionShellExecParams(command: 'npm test', cwd: '/app', timeout: 30000);

        expect($params->toArray())->toBe([
            'command' => 'npm test',
            'cwd' => '/app',
            'timeout' => 30000,
        ]);
    });
});

describe('SessionShellExecResult', function () {
    it('can be created with processId', function () {
        $result = new SessionShellExecResult(processId: 'proc-123');

        expect($result->processId)->toBe('proc-123');
    });

    it('can be created from array', function () {
        $result = SessionShellExecResult::fromArray(['processId' => 'proc-456']);

        expect($result->processId)->toBe('proc-456');
    });

    it('can convert to array', function () {
        $result = new SessionShellExecResult(processId: 'proc-789');

        expect($result->toArray())->toBe(['processId' => 'proc-789']);
    });
});

describe('SessionShellKillParams', function () {
    it('can be created with processId only', function () {
        $params = new SessionShellKillParams(processId: 'proc-123');

        expect($params->processId)->toBe('proc-123')
            ->and($params->signal)->toBeNull();
    });

    it('can be created with signal', function () {
        $params = new SessionShellKillParams(processId: 'proc-123', signal: 'SIGKILL');

        expect($params->processId)->toBe('proc-123')
            ->and($params->signal)->toBe('SIGKILL');
    });

    it('can be created from array', function () {
        $params = SessionShellKillParams::fromArray([
            'processId' => 'proc-456',
            'signal' => 'SIGTERM',
        ]);

        expect($params->processId)->toBe('proc-456')
            ->and($params->signal)->toBe('SIGTERM');
    });

    it('can be created from array without signal', function () {
        $params = SessionShellKillParams::fromArray(['processId' => 'proc-789']);

        expect($params->processId)->toBe('proc-789')
            ->and($params->signal)->toBeNull();
    });

    it('filters null signal in toArray', function () {
        $params = new SessionShellKillParams(processId: 'proc-123');

        expect($params->toArray())->toBe(['processId' => 'proc-123']);
    });

    it('includes signal in toArray when set', function () {
        $params = new SessionShellKillParams(processId: 'proc-123', signal: 'SIGKILL');

        expect($params->toArray())->toBe([
            'processId' => 'proc-123',
            'signal' => 'SIGKILL',
        ]);
    });
});

describe('SessionShellKillResult', function () {
    it('can be created with killed true', function () {
        $result = new SessionShellKillResult(killed: true);

        expect($result->killed)->toBeTrue();
    });

    it('can be created with killed false', function () {
        $result = new SessionShellKillResult(killed: false);

        expect($result->killed)->toBeFalse();
    });

    it('can be created from array', function () {
        $result = SessionShellKillResult::fromArray(['killed' => true]);

        expect($result->killed)->toBeTrue();
    });

    it('casts killed to bool from truthy value', function () {
        $result = SessionShellKillResult::fromArray(['killed' => 1]);

        expect($result->killed)->toBeTrue();
    });

    it('can convert to array', function () {
        $result = new SessionShellKillResult(killed: true);

        expect($result->toArray())->toBe(['killed' => true]);
    });
});

describe('SessionHistoryTruncateResult', function () {
    it('can be created from array', function () {
        $result = SessionHistoryTruncateResult::fromArray([
            'eventsRemoved' => 7,
        ]);

        expect($result->eventsRemoved)->toBe(7);
    });

    it('can convert to array', function () {
        $result = new SessionHistoryTruncateResult(eventsRemoved: 3);

        expect($result->toArray())->toBe([
            'eventsRemoved' => 3,
        ]);
    });
});

describe('SessionHistoryTruncateParams', function () {
    it('can be created from array', function () {
        $params = SessionHistoryTruncateParams::fromArray([
            'eventId' => 'evt-123',
        ]);

        expect($params->eventId)->toBe('evt-123');
    });

    it('can convert to array', function () {
        $params = new SessionHistoryTruncateParams(eventId: 'evt-456');

        expect($params->toArray())->toBe([
            'eventId' => 'evt-456',
        ]);
    });
});
