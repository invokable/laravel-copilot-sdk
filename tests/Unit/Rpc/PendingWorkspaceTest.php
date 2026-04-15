<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingWorkspace;
use Revolution\Copilot\Types\Rpc\WorkspaceCreateFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspaceListFilesResult;
use Revolution\Copilot\Types\Rpc\WorkspaceReadFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspaceReadFileResult;

describe('PendingWorkspace', function () {
    it('calls session.workspace.listFiles and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspace.listFiles',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'),
            )
            ->andReturn(['files' => ['README.md', 'src/main.php']]);

        $pending = new PendingWorkspace($client, 'test-session');
        $result = $pending->listFiles();

        expect($result)->toBeInstanceOf(WorkspaceListFilesResult::class)
            ->and($result->files)->toBe(['README.md', 'src/main.php']);
    });

    it('returns empty files list when workspace is empty', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->andReturn(['files' => []]);

        $pending = new PendingWorkspace($client, 'test-session');
        $result = $pending->listFiles();

        expect($result)->toBeInstanceOf(WorkspaceListFilesResult::class)
            ->and($result->files)->toBe([]);
    });

    it('calls session.workspace.readFile with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspace.readFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'README.md'),
            )
            ->andReturn(['content' => '# My Project']);

        $pending = new PendingWorkspace($client, 'test-session');
        $result = $pending->readFile(new WorkspaceReadFileRequest(path: 'README.md'));

        expect($result)->toBeInstanceOf(WorkspaceReadFileResult::class)
            ->and($result->content)->toBe('# My Project');
    });

    it('calls session.workspace.readFile with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspace.readFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'src/main.php'),
            )
            ->andReturn(['content' => '<?php echo "hello";']);

        $pending = new PendingWorkspace($client, 'test-session');
        $result = $pending->readFile(['path' => 'src/main.php']);

        expect($result)->toBeInstanceOf(WorkspaceReadFileResult::class)
            ->and($result->content)->toBe('<?php echo "hello";');
    });

    it('calls session.workspace.createFile with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspace.createFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'notes.txt'
                    && $params['content'] === 'hello world'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingWorkspace($client, 'test-session');
        $result = $pending->createFile(new WorkspaceCreateFileRequest(
            path: 'notes.txt',
            content: 'hello world',
        ));

        expect($result)->toBe(['success' => true]);
    });

    it('calls session.workspace.createFile with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.workspace.createFile',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['path'] === 'output.json'
                    && $params['content'] === '{"key":"value"}'),
            )
            ->andReturn([]);

        $pending = new PendingWorkspace($client, 'test-session');
        $result = $pending->createFile(['path' => 'output.json', 'content' => '{"key":"value"}']);

        expect($result)->toBe([]);
    });
});
