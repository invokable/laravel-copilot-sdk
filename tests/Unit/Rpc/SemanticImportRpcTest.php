<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\MemoriesImportParams;
use Revolution\Copilot\Types\Rpc\MemoriesImportResult;
use Revolution\Copilot\Types\Rpc\SessionsImportParams;
use Revolution\Copilot\Types\Rpc\SessionsImportResult;

describe('ServerRpc semantic imports', function () {
    it('sends sessions.import with typed or array parameters', function (bool $typed, bool $dryRun) {
        $params = [
            'jsonl' => "{\"type\":\"session\"}\n",
            'inputPath' => '/imports/session.jsonl',
            'defaultWorkingDirectory' => '/repo',
            'workingDirectory' => '/other',
            'name' => 'Imported',
            'dryRun' => $dryRun,
        ];
        $response = [
            'ok' => true, 'code' => 'ok', 'message' => 'validated',
            'dryRun' => $dryRun, 'imported' => [
                'sessions' => 1, 'messages' => 2, 'toolCalls' => 3, 'toolResults' => 4, 'attachments' => 5,
            ],
            'diagnostics' => [['line' => 2, 'code' => 'warning', 'message' => 'notice', 'externalId' => 'external']],
        ];
        if (! $dryRun) {
            $response['sessionId'] = 'new-session';
        }

        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->with('sessions.import', $params)->andReturn($response);

        $result = (new ServerRpc($client))->sessions()->import($typed ? SessionsImportParams::fromArray($params) : $params);

        expect($result)->toBeInstanceOf(SessionsImportResult::class)
            ->and($result->dryRun)->toBe($dryRun)
            ->and($result->sessionId)->toBe($dryRun ? null : 'new-session')
            ->and($result->imported->toolResults)->toBe(4)
            ->and($result->diagnostics[0]->externalId)->toBe('external');
    })->with([[true, true], [false, false]]);

    it('sends memories.import with typed or array parameters', function (bool $typed, bool $dryRun) {
        $params = [
            'jsonl' => "{\"type\":\"memory\"}\n",
            'workingDirectory' => '/repo',
            'dryRun' => $dryRun,
            'onConflict' => 'skip',
        ];
        $response = [
            'ok' => true, 'code' => 'ok', 'message' => 'validated',
            'dryRun' => $dryRun,
            'imported' => ['total' => 2, 'imported' => 1, 'skipped' => 1, 'failed' => 0],
            'diagnostics' => [],
            'records' => [['line' => 1, 'externalId' => 'memory-1', 'status' => 'imported', 'code' => 'ok', 'message' => 'done']],
        ];

        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->with('memories.import', $params)->andReturn($response);

        $result = (new ServerRpc($client))->memories()->import($typed ? MemoriesImportParams::fromArray($params) : $params);

        expect($result)->toBeInstanceOf(MemoriesImportResult::class)
            ->and($result->dryRun)->toBe($dryRun)
            ->and($result->imported->skipped)->toBe(1)
            ->and($result->records[0]->externalId)->toBe('memory-1');
    })->with([[true, false], [false, true]]);
});
