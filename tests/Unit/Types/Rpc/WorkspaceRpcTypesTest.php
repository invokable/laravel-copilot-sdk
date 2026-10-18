<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionsCreateWorkspaceRequest;
use Revolution\Copilot\Types\Rpc\SessionWorkingDirectoryContextWithClient;
use Revolution\Copilot\Types\Rpc\WorkspaceDiffResult;
use Revolution\Copilot\Types\Rpc\WorkspacesAddSummaryRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesCheckpoints;
use Revolution\Copilot\Types\Rpc\WorkspacesDiffRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesListCheckpointsResult;
use Revolution\Copilot\Types\Rpc\WorkspacesReadCheckpointRequest;
use Revolution\Copilot\Types\Rpc\WorkspacesSaveLargePasteResult;
use Revolution\Copilot\Types\Rpc\WorkspacesTruncateSummariesRequest;

describe('workspace RPC types', function () {
    it('round trips checkpoint and summary request fields with their wire names', function () {
        $checkpoints = WorkspacesListCheckpointsResult::fromArray([
            'checkpoints' => [['filename' => 'checkpoint-4.md', 'number' => 4, 'title' => 'Summary']],
        ]);
        $read = WorkspacesReadCheckpointRequest::fromArray(['number' => 4]);
        $summary = WorkspacesAddSummaryRequest::fromArray(['title' => 'Summary', 'content' => 'Details']);
        $truncate = WorkspacesTruncateSummariesRequest::fromArray(['keepCount' => 2]);

        expect($checkpoints->checkpoints[0])->toBeInstanceOf(WorkspacesCheckpoints::class)
            ->and($checkpoints->toArray())->toBe([
                'checkpoints' => [['filename' => 'checkpoint-4.md', 'number' => 4, 'title' => 'Summary']],
            ])
            ->and($read->toArray())->toBe(['number' => 4])
            ->and($summary->toArray())->toBe(['title' => 'Summary', 'content' => 'Details'])
            ->and($truncate->toArray())->toBe(['keepCount' => 2]);
    });

    it('preserves false diff options and maps diff response fields exactly', function () {
        $request = WorkspacesDiffRequest::fromArray(['mode' => 'session', 'ignoreWhitespace' => false]);
        $result = WorkspaceDiffResult::fromArray([
            'baseBranch' => 'main',
            'changes' => [[
                'changeType' => 'renamed',
                'diff' => '@@ -1 +1 @@',
                'isTruncated' => false,
                'oldPath' => 'old.txt',
                'path' => 'new.txt',
            ]],
            'isFallback' => true,
            'mode' => 'unstaged',
            'requestedMode' => 'session',
            'unavailableReason' => 'session-busy',
        ]);

        expect($request->toArray())->toBe(['mode' => 'session', 'ignoreWhitespace' => false])
            ->and($result->toArray())->toBe([
                'baseBranch' => 'main',
                'changes' => [[
                    'changeType' => 'renamed',
                    'diff' => '@@ -1 +1 @@',
                    'isTruncated' => false,
                    'oldPath' => 'old.txt',
                    'path' => 'new.txt',
                ]],
                'isFallback' => true,
                'mode' => 'unstaged',
                'requestedMode' => 'session',
                'unavailableReason' => 'session-busy',
            ]);
    });

    it('maps saved paste fields and nullable workspace availability', function () {
        $saved = WorkspacesSaveLargePasteResult::fromArray([
            'saved' => ['filename' => 'paste.txt', 'filePath' => '/workspace/paste.txt', 'sizeBytes' => 128],
        ]);
        $unavailable = WorkspacesSaveLargePasteResult::fromArray(['saved' => null]);

        expect($saved->toArray())->toBe([
            'saved' => ['filename' => 'paste.txt', 'filePath' => '/workspace/paste.txt', 'sizeBytes' => 128],
        ])
            ->and($unavailable->saved)->toBeNull()
            ->and($unavailable->toArray())->toBe(['saved' => null]);
    });

    it('requires the server workspace state path and maps context fields as camel case', function () {
        $request = SessionsCreateWorkspaceRequest::fromArray([
            'sessionId' => 'session-1',
            'convention' => 'copilot',
            'sessionStatePath' => '/sessions/session-1',
            'context' => [
                'cwd' => '/repo',
                'clientName' => 'vscode',
                'repositoryHost' => 'github.com',
            ],
        ]);

        expect($request->context)->toBeInstanceOf(SessionWorkingDirectoryContextWithClient::class)
            ->and($request->toArray())->toBe([
                'sessionId' => 'session-1',
                'convention' => 'copilot',
                'context' => [
                    'clientName' => 'vscode',
                    'cwd' => '/repo',
                    'repositoryHost' => 'github.com',
                ],
                'sessionStatePath' => '/sessions/session-1',
            ]);
    });
});
