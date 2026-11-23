<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ImportDiagnostic;
use Revolution\Copilot\Types\Rpc\MemoriesImportParams;
use Revolution\Copilot\Types\Rpc\MemoriesImportResult;
use Revolution\Copilot\Types\Rpc\MemoryImportCounts;
use Revolution\Copilot\Types\Rpc\MemoryImportItemResult;
use Revolution\Copilot\Types\Rpc\SessionImportCounts;
use Revolution\Copilot\Types\Rpc\SessionsImportParams;
use Revolution\Copilot\Types\Rpc\SessionsImportResult;

describe('Semantic import types', function () {
    it('round trips request options including false and omits nulls', function () {
        $sessions = ['jsonl' => 'data', 'inputPath' => '/input', 'defaultWorkingDirectory' => '/repo', 'dryRun' => false];
        $memories = ['jsonl' => 'data', 'workingDirectory' => '/repo', 'dryRun' => false, 'onConflict' => 'error'];

        expect(SessionsImportParams::fromArray($sessions)->toArray())->toBe($sessions)
            ->and(MemoriesImportParams::fromArray($memories)->toArray())->toBe($memories)
            ->and((new MemoriesImportParams(jsonl: 'data', workingDirectory: '/repo'))->toArray())
            ->toBe(['jsonl' => 'data', 'workingDirectory' => '/repo']);
    });

    it('round trips session import results with nested diagnostics and counts', function () {
        $data = [
            'ok' => false, 'code' => 'invalid', 'message' => 'error',
            'externalId' => 'external', 'sessionId' => 'session', 'dryRun' => true,
            'imported' => ['sessions' => 0, 'messages' => 1, 'toolCalls' => 2, 'toolResults' => 3, 'attachments' => 4],
            'diagnostics' => [
                ['line' => 1, 'code' => 'invalid', 'message' => 'bad', 'externalId' => 'external'],
                ['line' => 2, 'code' => 'warning', 'message' => 'notice'],
            ],
        ];
        $result = SessionsImportResult::fromArray($data);

        expect($result->imported)->toBeInstanceOf(SessionImportCounts::class)
            ->and($result->diagnostics[0])->toBeInstanceOf(ImportDiagnostic::class)
            ->and($result->diagnostics[1]->externalId)->toBeNull()
            ->and($result->toArray())->toBe($data);
    });

    it('round trips memory import results with nested records and counts', function () {
        $data = [
            'ok' => true, 'code' => 'ok', 'message' => 'done', 'dryRun' => false,
            'imported' => ['total' => 2, 'imported' => 1, 'skipped' => 1, 'failed' => 0],
            'diagnostics' => [['line' => 1, 'code' => 'warning', 'message' => 'notice']],
            'records' => [
                ['line' => 1, 'externalId' => 'external', 'status' => 'imported', 'code' => 'ok', 'message' => 'done'],
                ['line' => 2, 'status' => 'skipped', 'code' => 'conflict', 'message' => 'exists'],
            ],
        ];
        $result = MemoriesImportResult::fromArray($data);

        expect($result->imported)->toBeInstanceOf(MemoryImportCounts::class)
            ->and($result->diagnostics[0])->toBeInstanceOf(ImportDiagnostic::class)
            ->and($result->records[0])->toBeInstanceOf(MemoryImportItemResult::class)
            ->and($result->records[1]->externalId)->toBeNull()
            ->and($result->toArray())->toBe($data);
    });
});
