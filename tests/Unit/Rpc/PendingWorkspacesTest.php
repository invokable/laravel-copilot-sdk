<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingWorkspaces;
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

describe('PendingWorkspaces', function () {
    it('calls session.workspaces.getWorkspace and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.getWorkspace',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'),
            )
            ->andReturn(['workspace' => ['id' => 'ws-1', 'branch' => 'main']]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->getWorkspace();

        expect($result)->toBeInstanceOf(WorkspacesGetWorkspaceResult::class)
            ->and($result->workspace)->not->toBeNull()
            ->and($result->workspace->id)->toBe('ws-1')
            ->and($result->workspace->branch)->toBe('main');
    });

    it('returns null workspace when not available', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->andReturn(['workspace' => null]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->getWorkspace();

        expect($result)->toBeInstanceOf(WorkspacesGetWorkspaceResult::class)
            ->and($result->workspace)->toBeNull();
    });

    it('calls session.workspaces.listFiles and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.listFiles',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'),
            )
            ->andReturn(['files' => ['README.md', 'src/main.php']]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->listFiles();

        expect($result)->toBeInstanceOf(WorkspacesListFilesResult::class)
            ->and($result->files)->toBe(['README.md', 'src/main.php']);
    });

    it('returns empty files list when workspace is empty', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->andReturn(['files' => []]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->listFiles();

        expect($result)->toBeInstanceOf(WorkspacesListFilesResult::class)
            ->and($result->files)->toBe([]);
    });

    it('calls session.workspaces.readFile with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.readFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'README.md'),
            )
            ->andReturn(['content' => '# My Project']);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->readFile(new WorkspacesReadFileRequest(path: 'README.md'));

        expect($result)->toBeInstanceOf(WorkspacesReadFileResult::class)
            ->and($result->content)->toBe('# My Project');
    });

    it('calls session.workspaces.readFile with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.readFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'src/main.php'),
            )
            ->andReturn(['content' => '<?php echo "hello";']);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->readFile(['path' => 'src/main.php']);

        expect($result)->toBeInstanceOf(WorkspacesReadFileResult::class)
            ->and($result->content)->toBe('<?php echo "hello";');
    });

    it('calls session.workspaces.createFile with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.createFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'notes.txt'
                    && $params['content'] === 'hello world'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->createFile(new WorkspacesCreateFileRequest(
            path: 'notes.txt',
            content: 'hello world',
        ));

        expect($result)->toBe(['success' => true]);
    });

    it('calls session.workspaces.createFile with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.createFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'output.json'
                    && $params['content'] === '{"key":"value"}'),
            )
            ->andReturn([]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->createFile(['path' => 'output.json', 'content' => '{"key":"value"}']);

        expect($result)->toBe([]);
    });

    it('calls session.workspaces.statFile with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.statFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'README.md'),
            )
            ->andReturn([
                'isFile' => true,
                'isDirectory' => false,
                'size' => 1024,
                'mtimeMs' => 1700000000000,
                'birthtimeMs' => 1690000000000,
            ]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->statFile(new WorkspacesStatFileRequest(path: 'README.md'));

        expect($result)->toBeInstanceOf(WorkspacesStatFileResult::class)
            ->and($result->isFile)->toBeTrue()
            ->and($result->isDirectory)->toBeFalse()
            ->and($result->size)->toBe(1024.0);
    });

    it('calls session.workspaces.statFile with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.statFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'src'),
            )
            ->andReturn([
                'isFile' => false,
                'isDirectory' => true,
                'size' => 0,
                'mtimeMs' => 0,
                'birthtimeMs' => 0,
            ]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->statFile(['path' => 'src']);

        expect($result)->toBeInstanceOf(WorkspacesStatFileResult::class)
            ->and($result->isDirectory)->toBeTrue();
    });

    it('calls session.workspaces.createDirectory with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.createDirectory',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'new/dir'
                    && $params['recursive'] === true),
            )
            ->andReturn([]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->createDirectory(new WorkspacesCreateDirectoryRequest(path: 'new/dir', recursive: true));

        expect($result)->toBe([]);
    });

    it('calls session.workspaces.createDirectory with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.createDirectory',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'another/dir'),
            )
            ->andReturn([]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->createDirectory(['path' => 'another/dir']);

        expect($result)->toBe([]);
    });

    it('calls session.workspaces.removePath with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.removePath',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'old/dir'
                    && $params['recursive'] === true
                    && $params['force'] === true),
            )
            ->andReturn([]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->removePath(new WorkspacesRemovePathRequest(path: 'old/dir', recursive: true, force: true));

        expect($result)->toBe([]);
    });

    it('calls session.workspaces.removePath with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.removePath',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'file.txt'),
            )
            ->andReturn([]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->removePath(['path' => 'file.txt']);

        expect($result)->toBe([]);
    });

    it('calls session.workspaces.renamePath with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.renamePath',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['source'] === 'old.txt'
                    && $params['destination'] === 'new.txt'),
            )
            ->andReturn([]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->renamePath(new WorkspacesRenamePathRequest(source: 'old.txt', destination: 'new.txt'));

        expect($result)->toBe([]);
    });

    it('calls session.workspaces.renamePath with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspaces.renamePath',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['source'] === 'a.txt'
                    && $params['destination'] === 'b.txt'),
            )
            ->andReturn([]);

        $pending = new PendingWorkspaces($client, 'test-session');
        $result = $pending->renamePath(['source' => 'a.txt', 'destination' => 'b.txt']);

        expect($result)->toBe([]);
    });
});
