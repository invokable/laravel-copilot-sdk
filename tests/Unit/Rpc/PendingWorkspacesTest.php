<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingWorkspaces;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesGetWorkspaceResult;
use Revolution\Copilot\Types\Rpc\WorkspacesListFilesResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileResult;

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
});
