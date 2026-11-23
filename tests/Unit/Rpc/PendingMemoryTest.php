<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\MemoryImportScope;
use Revolution\Copilot\Enums\MemoryImportStatus;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMemory;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\SessionMemoryImportRecordRequest;
use Revolution\Copilot\Types\Rpc\SessionMemoryImportRecordResult;

describe('Session memory import', function () {
    it('sends one record with the session ID and returns the service outcome', function (bool $typed, string $status, bool $ok) {
        $params = [
            'scope' => 'repository',
            'subject' => 'Coding style',
            'fact' => 'Use strict types',
            'reason' => 'Maintain consistency',
            'citations' => 'Project guidelines',
        ];
        $response = ['ok' => $ok, 'status' => $status, 'code' => 'service-code', 'message' => 'Service response'];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('session.memory.importRecord', [...$params, 'sessionId' => 'session-1'])
            ->andReturn($response);

        $memory = (new SessionRpc($client, 'session-1'))->memory();
        $result = $memory->importRecord($typed
            ? new SessionMemoryImportRecordRequest(MemoryImportScope::Repository, ...array_values(array_slice($params, 1)))
            : [...$params, 'sessionId' => 'other-session']);

        expect($memory)->toBeInstanceOf(PendingMemory::class)
            ->and($result)->toBeInstanceOf(SessionMemoryImportRecordResult::class)
            ->and($result->ok)->toBe($ok)
            ->and($result->status)->toBe(MemoryImportStatus::from($status))
            ->and($result->code)->toBe('service-code')
            ->and($result->message)->toBe('Service response')
            ->and($result->toArray())->toBe($response);
    })->with([
        [true, 'imported', true],
        [false, 'conflict', false],
        [false, 'disabled', false],
        [false, 'unavailable', false],
        [false, 'failed', false],
    ]);

    it('propagates JSON-RPC errors', function () {
        $params = ['scope' => 'user', 'subject' => 'subject', 'fact' => 'fact', 'reason' => 'reason', 'citations' => 'source'];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('session.memory.importRecord', [...$params, 'sessionId' => 'session-1'])
            ->andThrow(new JsonRpcException(-32000, 'Import failed'));

        (new SessionRpc($client, 'session-1'))->memory()->importRecord($params);
    })->throws(JsonRpcException::class, 'Import failed');
});
