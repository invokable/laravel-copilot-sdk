<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingWorkspaces;
use Revolution\Copilot\Types\Rpc\WorkspaceDiffResult;
use Revolution\Copilot\Types\Rpc\WorkspacesAddSummaryResult;
use Revolution\Copilot\Types\Rpc\WorkspacesAutopilotObjectiveExistsResult;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateDirectoryRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesCreateFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesDeleteAutopilotObjectiveResult;
use Revolution\Copilot\Types\Rpc\WorkspacesGetWorkspaceResult;
use Revolution\Copilot\Types\Rpc\WorkspacesListCheckpointsResult;
use Revolution\Copilot\Types\Rpc\WorkspacesListFilesResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadAutopilotObjectiveResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadCheckpointResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesReadFileResult;
use Revolution\Copilot\Types\Rpc\WorkspacesRemovePathRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesRenamePathRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesSaveLargePasteResult;
use Revolution\Copilot\Types\Rpc\WorkspacesStatFileRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesStatFileResult;
use Revolution\Copilot\Types\Rpc\WorkspacesWriteAutopilotObjectiveResult;

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

    it('dispatches all workspace metadata, checkpoint, autopilot, paste, and diff operations', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.updateMetadata', [
                'context' => ['cwd' => '/repo'],
                'name' => 'Repository',
                'sessionId' => 'session-workspaces',
            ])
            ->andReturn(['path' => '/workspaces/repo', 'workspace' => ['id' => 'ws-1']]);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.ensure', [
                'context' => ['cwd' => '/repo'],
                'sessionId' => 'session-workspaces',
            ])
            ->andReturn(['workspace' => ['id' => 'ws-1']]);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.listCheckpoints', ['sessionId' => 'session-workspaces'])
            ->andReturn(['checkpoints' => [['filename' => 'checkpoint-1.md', 'number' => 1, 'title' => 'Initial']]]);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.readCheckpoint', ['number' => 1, 'sessionId' => 'session-workspaces'])
            ->andReturn(['content' => '# Initial']);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.addSummary', [
                'title' => 'Summary',
                'content' => 'Current state',
                'sessionId' => 'session-workspaces',
            ])
            ->andReturn(['summary' => ['title' => 'Summary'], 'workspace' => ['summary_count' => 1]]);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.truncateSummaries', [
                'keepCount' => 2,
                'sessionId' => 'session-workspaces',
            ])
            ->andReturn(['workspace' => ['id' => 'ws-1']]);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.readAutopilotObjective', ['sessionId' => 'session-workspaces'])
            ->andReturn(['content' => 'Finish the task']);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.writeAutopilotObjective', [
                'content' => 'Finish the task',
                'sessionId' => 'session-workspaces',
            ])
            ->andReturn(['operation' => 'created']);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.deleteAutopilotObjective', ['sessionId' => 'session-workspaces'])
            ->andReturn(['deleted' => true]);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.autopilotObjectiveExists', ['sessionId' => 'session-workspaces'])
            ->andReturn(['exists' => true]);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.saveLargePaste', [
                'content' => 'Large pasted content',
                'sessionId' => 'session-workspaces',
            ])
            ->andReturn(['saved' => ['filename' => 'paste-1.txt', 'filePath' => '/workspace/paste-1.txt', 'sizeBytes' => 20]]);
        $client->shouldReceive('request')->once()
            ->with('session.workspaces.diff', [
                'mode' => 'branch',
                'ignoreWhitespace' => false,
                'sessionId' => 'session-workspaces',
            ])
            ->andReturn([
                'mode' => 'branch',
                'requestedMode' => 'branch',
                'changes' => [[
                    'changeType' => 'modified',
                    'diff' => '@@ -1 +1 @@',
                    'isTruncated' => false,
                    'oldPath' => null,
                    'path' => 'README.md',
                ]],
            ]);

        $pending = new PendingWorkspaces($client, 'session-workspaces');
        $updated = $pending->updateMetadata(['sessionId' => 'untrusted', 'context' => ['cwd' => '/repo'], 'name' => 'Repository']);
        $ensured = $pending->ensure(['context' => ['cwd' => '/repo']]);
        $checkpoints = $pending->listCheckpoints();
        $checkpoint = $pending->readCheckpoint(['number' => 1]);
        $summary = $pending->addSummary(['title' => 'Summary', 'content' => 'Current state']);
        $truncated = $pending->truncateSummaries(['keepCount' => 2]);
        $objective = $pending->readAutopilotObjective();
        $written = $pending->writeAutopilotObjective(['content' => 'Finish the task']);
        $deleted = $pending->deleteAutopilotObjective();
        $exists = $pending->autopilotObjectiveExists();
        $paste = $pending->saveLargePaste(['content' => 'Large pasted content']);
        $diff = $pending->diff(['mode' => 'branch', 'ignoreWhitespace' => false]);

        expect($updated)->toBeInstanceOf(WorkspacesGetWorkspaceResult::class)
            ->and($updated->path)->toBe('/workspaces/repo')
            ->and($ensured->workspace?->id)->toBe('ws-1')
            ->and($checkpoints)->toBeInstanceOf(WorkspacesListCheckpointsResult::class)
            ->and($checkpoints->checkpoints[0]->filename)->toBe('checkpoint-1.md')
            ->and($checkpoint)->toBeInstanceOf(WorkspacesReadCheckpointResult::class)
            ->and($checkpoint->content)->toBe('# Initial')
            ->and($summary)->toBeInstanceOf(WorkspacesAddSummaryResult::class)
            ->and($summary->summary?->data['title'])->toBe('Summary')
            ->and($truncated)->toBeInstanceOf(WorkspacesGetWorkspaceResult::class)
            ->and($objective)->toBeInstanceOf(WorkspacesReadAutopilotObjectiveResult::class)
            ->and($objective->content)->toBe('Finish the task')
            ->and($written)->toBeInstanceOf(WorkspacesWriteAutopilotObjectiveResult::class)
            ->and($written->operation)->toBe('created')
            ->and($deleted)->toBeInstanceOf(WorkspacesDeleteAutopilotObjectiveResult::class)
            ->and($deleted->deleted)->toBeTrue()
            ->and($exists)->toBeInstanceOf(WorkspacesAutopilotObjectiveExistsResult::class)
            ->and($exists->exists)->toBeTrue()
            ->and($paste)->toBeInstanceOf(WorkspacesSaveLargePasteResult::class)
            ->and($paste->saved?->filePath)->toBe('/workspace/paste-1.txt')
            ->and($diff)->toBeInstanceOf(WorkspaceDiffResult::class)
            ->and($diff->changes[0]->path)->toBe('README.md');
    });
});
