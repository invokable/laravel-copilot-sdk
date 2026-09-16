<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\NameGetResult;
use Revolution\Copilot\Types\Rpc\NameSetRequest;
use Revolution\Copilot\Types\Rpc\Workspace;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateDirectoryRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesGetWorkspaceResult;
use Revolution\Copilot\Types\Rpc\WorkspacesListFilesResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileResult;
use Revolution\Copilot\Types\Rpc\WorkspacesRemovePathRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesRenamePathRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesStatFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesStatFileResult;

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
            'pr_create_sync_dismissed' => true,
            'chronicle_sync_dismissed' => false,
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
            ->and($ws->prCreateSyncDismissed)->toBeTrue()
            ->and($ws->chronicleSyncDismissed)->toBeFalse();
    });

    it('handles minimal data', function () {
        $ws = Workspace::fromArray(['id' => 'ws-min']);

        expect($ws->id)->toBe('ws-min')
            ->and($ws->cwd)->toBeNull()
            ->and($ws->branch)->toBeNull()
            ->and($ws->hostType)->toBeNull()
            ->and($ws->prCreateSyncDismissed)->toBeNull()
            ->and($ws->chronicleSyncDismissed)->toBeNull();
    });

    it('converts to array with snake_case keys', function () {
        $ws = Workspace::fromArray([
            'id' => 'ws-1',
            'git_root' => '/root',
            'host_type' => 'ado',
        ]);

        $arr = $ws->toArray();

        expect($arr)->toHaveKey('id', 'ws-1')
            ->and($arr)->toHaveKey('git_root', '/root')
            ->and($arr)->toHaveKey('host_type', 'ado')
            ->and($arr)->not->toHaveKey('session_sync_level');
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

describe('WorkspacesCreateDirectoryRequest', function () {
    it('can be created with defaults', function () {
        $req = new WorkspacesCreateDirectoryRequest(path: 'new/dir');

        expect($req->path)->toBe('new/dir')
            ->and($req->recursive)->toBeNull()
            ->and($req->toArray())->toBe(['path' => 'new/dir']);
    });

    it('can be created with recursive flag', function () {
        $req = WorkspacesCreateDirectoryRequest::fromArray(['path' => 'a/b/c', 'recursive' => true]);

        expect($req->recursive)->toBeTrue()
            ->and($req->toArray())->toBe(['path' => 'a/b/c', 'recursive' => true]);
    });
});

describe('WorkspacesRemovePathRequest', function () {
    it('can be created with defaults', function () {
        $req = new WorkspacesRemovePathRequest(path: 'file.txt');

        expect($req->path)->toBe('file.txt')
            ->and($req->recursive)->toBeNull()
            ->and($req->force)->toBeNull()
            ->and($req->toArray())->toBe(['path' => 'file.txt']);
    });

    it('can be created with recursive and force flags', function () {
        $req = WorkspacesRemovePathRequest::fromArray(['path' => 'dir', 'recursive' => true, 'force' => true]);

        expect($req->recursive)->toBeTrue()
            ->and($req->force)->toBeTrue()
            ->and($req->toArray())->toBe(['path' => 'dir', 'recursive' => true, 'force' => true]);
    });
});

describe('WorkspacesRenamePathRequest', function () {
    it('can be created', function () {
        $req = new WorkspacesRenamePathRequest(source: 'old.txt', destination: 'new.txt');

        expect($req->source)->toBe('old.txt')
            ->and($req->destination)->toBe('new.txt')
            ->and($req->toArray())->toBe(['source' => 'old.txt', 'destination' => 'new.txt']);
    });

    it('can be created from array', function () {
        $req = WorkspacesRenamePathRequest::fromArray(['source' => 'a', 'destination' => 'b']);

        expect($req->source)->toBe('a')
            ->and($req->destination)->toBe('b');
    });
});

describe('WorkspacesStatFileRequest', function () {
    it('can be created', function () {
        $req = new WorkspacesStatFileRequest(path: 'file.txt');

        expect($req->path)->toBe('file.txt')
            ->and($req->toArray())->toBe(['path' => 'file.txt']);
    });
});

describe('WorkspacesStatFileResult', function () {
    it('can be created from array', function () {
        $result = WorkspacesStatFileResult::fromArray([
            'isFile' => true,
            'isDirectory' => false,
            'size' => 2048,
            'mtimeMs' => 1700000000000,
            'birthtimeMs' => 1690000000000,
        ]);

        expect($result->isFile)->toBeTrue()
            ->and($result->isDirectory)->toBeFalse()
            ->and($result->size)->toBe(2048.0)
            ->and($result->mtimeMs)->toBe(1700000000000.0)
            ->and($result->birthtimeMs)->toBe(1690000000000.0);
    });

    it('defaults numeric and boolean fields when missing', function () {
        $result = WorkspacesStatFileResult::fromArray([]);

        expect($result->isFile)->toBeFalse()
            ->and($result->isDirectory)->toBeFalse()
            ->and($result->size)->toBe(0.0);
    });

    it('converts to array', function () {
        $result = new WorkspacesStatFileResult(isFile: true, isDirectory: false, size: 10.0, mtimeMs: 1.0, birthtimeMs: 2.0);

        expect($result->toArray())->toBe([
            'isFile' => true,
            'isDirectory' => false,
            'size' => 10.0,
            'mtimeMs' => 1.0,
            'birthtimeMs' => 2.0,
        ]);
    });
});
