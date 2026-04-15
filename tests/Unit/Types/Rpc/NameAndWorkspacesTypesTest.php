<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\NameGetResult;
use Revolution\Copilot\Types\Rpc\NameSetRequest;
use Revolution\Copilot\Types\Rpc\Workspace;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesGetWorkspaceResult;
use Revolution\Copilot\Types\Rpc\WorkspacesListFilesResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileResult;

describe('NameGetResult', function () {
    it('can be created with a name', function () {
        $result = NameGetResult::fromArray(['name' => 'My Session']);

        expect($result->name)->toBe('My Session');
    });

    it('handles null name', function () {
        $result = NameGetResult::fromArray(['name' => null]);

        expect($result->name)->toBeNull();
    });

    it('handles missing name', function () {
        $result = NameGetResult::fromArray([]);

        expect($result->name)->toBeNull();
    });

    it('converts to array', function () {
        $result = NameGetResult::fromArray(['name' => 'Test']);

        expect($result->toArray())->toBe(['name' => 'Test']);
    });
});

describe('NameSetRequest', function () {
    it('can be created with a name', function () {
        $request = new NameSetRequest(name: 'New Session Name');

        expect($request->name)->toBe('New Session Name');
    });

    it('can be created from array', function () {
        $request = NameSetRequest::fromArray(['name' => 'From Array']);

        expect($request->name)->toBe('From Array');
    });

    it('converts to array', function () {
        $request = new NameSetRequest(name: 'Test');

        expect($request->toArray())->toBe(['name' => 'Test']);
    });
});

describe('Workspace', function () {
    it('can be created with all fields', function () {
        $ws = Workspace::fromArray([
            'id' => 'ws-123',
            'cwd' => '/home/user/project',
            'git_root' => '/home/user/project',
            'repository' => 'user/repo',
            'host_type' => 'github',
            'branch' => 'main',
            'summary' => 'A project',
            'name' => 'my-workspace',
            'summary_count' => 5,
            'created_at' => '2025-01-01T00:00:00Z',
            'updated_at' => '2025-06-01T00:00:00Z',
            'mc_task_id' => 'task-1',
            'mc_session_id' => 'sess-1',
            'mc_last_event_id' => 'evt-1',
            'session_sync_level' => 'repo_and_user',
        ]);

        expect($ws->id)->toBe('ws-123')
            ->and($ws->cwd)->toBe('/home/user/project')
            ->and($ws->gitRoot)->toBe('/home/user/project')
            ->and($ws->repository)->toBe('user/repo')
            ->and($ws->hostType)->toBe('github')
            ->and($ws->branch)->toBe('main')
            ->and($ws->summary)->toBe('A project')
            ->and($ws->name)->toBe('my-workspace')
            ->and($ws->summaryCount)->toBe(5)
            ->and($ws->createdAt)->toBe('2025-01-01T00:00:00Z')
            ->and($ws->updatedAt)->toBe('2025-06-01T00:00:00Z')
            ->and($ws->mcTaskId)->toBe('task-1')
            ->and($ws->mcSessionId)->toBe('sess-1')
            ->and($ws->mcLastEventId)->toBe('evt-1')
            ->and($ws->sessionSyncLevel)->toBe('repo_and_user');
    });

    it('handles minimal data', function () {
        $ws = Workspace::fromArray(['id' => 'ws-min']);

        expect($ws->id)->toBe('ws-min')
            ->and($ws->cwd)->toBeNull()
            ->and($ws->branch)->toBeNull()
            ->and($ws->hostType)->toBeNull()
            ->and($ws->sessionSyncLevel)->toBeNull();
    });

    it('converts to array with snake_case keys', function () {
        $ws = Workspace::fromArray([
            'id' => 'ws-1',
            'git_root' => '/root',
            'host_type' => 'ado',
            'session_sync_level' => 'local',
        ]);

        $arr = $ws->toArray();

        expect($arr)->toHaveKey('id', 'ws-1')
            ->and($arr)->toHaveKey('git_root', '/root')
            ->and($arr)->toHaveKey('host_type', 'ado')
            ->and($arr)->toHaveKey('session_sync_level', 'local');
    });
});

describe('WorkspacesGetWorkspaceResult', function () {
    it('can be created with workspace data', function () {
        $result = WorkspacesGetWorkspaceResult::fromArray([
            'workspace' => ['id' => 'ws-1', 'branch' => 'main'],
        ]);

        expect($result->workspace)->not->toBeNull()
            ->and($result->workspace->id)->toBe('ws-1')
            ->and($result->workspace->branch)->toBe('main');
    });

    it('handles null workspace', function () {
        $result = WorkspacesGetWorkspaceResult::fromArray(['workspace' => null]);

        expect($result->workspace)->toBeNull();
    });

    it('handles missing workspace', function () {
        $result = WorkspacesGetWorkspaceResult::fromArray([]);

        expect($result->workspace)->toBeNull();
    });

    it('converts to array', function () {
        $result = WorkspacesGetWorkspaceResult::fromArray([
            'workspace' => ['id' => 'ws-1'],
        ]);

        expect($result->toArray()['workspace'])->toHaveKey('id', 'ws-1');
    });
});

describe('WorkspacesListFilesResult', function () {
    it('can be created from array', function () {
        $result = WorkspacesListFilesResult::fromArray([
            'files' => ['file1.txt', 'file2.txt'],
        ]);

        expect($result->files)->toBe(['file1.txt', 'file2.txt']);
    });

    it('handles empty files', function () {
        $result = WorkspacesListFilesResult::fromArray([]);

        expect($result->files)->toBe([]);
    });

    it('converts to array', function () {
        $result = WorkspacesListFilesResult::fromArray(['files' => ['a.txt']]);

        expect($result->toArray())->toBe(['files' => ['a.txt']]);
    });
});

describe('WorkspacesReadFileResult', function () {
    it('can be created from array', function () {
        $result = WorkspacesReadFileResult::fromArray(['content' => 'hello']);

        expect($result->content)->toBe('hello');
    });

    it('converts to array', function () {
        $result = new WorkspacesReadFileResult(content: 'test');

        expect($result->toArray())->toBe(['content' => 'test']);
    });
});

describe('WorkspacesReadFileRequest', function () {
    it('can be created', function () {
        $req = new WorkspacesReadFileRequest(path: 'test.txt');

        expect($req->path)->toBe('test.txt')
            ->and($req->toArray())->toBe(['path' => 'test.txt']);
    });
});

describe('WorkspacesCreateFileRequest', function () {
    it('can be created', function () {
        $req = new WorkspacesCreateFileRequest(path: 'test.txt', content: 'hello');

        expect($req->path)->toBe('test.txt')
            ->and($req->content)->toBe('hello')
            ->and($req->toArray())->toBe(['path' => 'test.txt', 'content' => 'hello']);
    });
});
