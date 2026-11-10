<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingLsp;
use Revolution\Copilot\Types\Rpc\LspInitializeRequest;
use Revolution\Copilot\Types\Rpc\SessionLspDiagnoseRequest;
use Revolution\Copilot\Types\Rpc\SessionLspDiagnoseResult;
use Revolution\Copilot\Types\Rpc\SessionLspListResult;
use Revolution\Copilot\Types\Rpc\SessionLspSetRequest;
use Revolution\Copilot\Types\Rpc\SessionLspStartResult;

describe('PendingLsp', function () {
    it('lists session language-server configurations from typed and array parameters', function () {
        $response = [
            'configs' => [[
                'id' => 'php',
                'fileExtensions' => ['php' => 'php'],
                'disabled' => false,
            ]],
            'userServers' => [],
            'pluginServers' => [],
            'projectServers' => [],
            'userConfigPath' => '/home/user/.config/copilot/lsp.json',
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.lsp.list', [
                'workingDirectory' => '/workspace',
                'force' => true,
                'sessionId' => 'session-123',
            ])
            ->andReturn($response, $response);

        $pending = new PendingLsp($client, 'session-123');
        $typedResult = $pending->list(new LspInitializeRequest(
            workingDirectory: '/workspace',
            force: true,
        ));
        $arrayResult = $pending->list([
            'workingDirectory' => '/workspace',
            'force' => true,
        ]);

        expect($typedResult)->toBeInstanceOf(SessionLspListResult::class)
            ->and($typedResult->configs[0]->id)->toBe('php')
            ->and($typedResult->configs[0]->fileExtensions)->toBe(['php' => 'php'])
            ->and($arrayResult->userConfigPath)->toBe('/home/user/.config/copilot/lsp.json');
    });

    it('starts session language servers and returns warmup errors', function () {
        $response = [
            'servers' => ['php'],
            'errors' => [['serverId' => 'rust', 'error' => 'binary not found']],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.lsp.start', [
                'workingDirectory' => '/workspace',
                'sessionId' => 'session-123',
            ])
            ->andReturn($response, $response);

        $pending = new PendingLsp($client, 'session-123');
        $typedResult = $pending->start(new LspInitializeRequest(workingDirectory: '/workspace'));
        $arrayResult = $pending->start(['workingDirectory' => '/workspace']);

        expect($typedResult)->toBeInstanceOf(SessionLspStartResult::class)
            ->and($typedResult->servers)->toBe(['php'])
            ->and($typedResult->errors[0]->serverId)->toBe('rust')
            ->and($typedResult->errors[0]->error)->toBe('binary not found')
            ->and($arrayResult->errors[0]->serverId)->toBe('rust');
    });

    it('stops language servers for the current session', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.lsp.stop', ['sessionId' => 'session-123'])
            ->andReturnNull();

        (new PendingLsp($client, 'session-123'))->stop();
    });

    it('diagnoses a language server using the session identifier', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.lsp.diagnose', [
                'serverId' => 'php',
                'sessionId' => 'session-123',
            ])
            ->andReturn(
                [
                    'status' => 'started',
                    'availableServers' => ['php'],
                    'pid' => 101,
                    'spawnTimeMs' => 42,
                    'immediateExit' => ['code' => 1, 'signal' => null],
                    'sandboxed' => true,
                ],
                [
                    'status' => 'started',
                    'availableServers' => ['php'],
                    'pid' => null,
                    'spawnTimeMs' => 42,
                    'sandboxed' => false,
                ],
            );

        $pending = new PendingLsp($client, 'session-123');
        $typedResult = $pending->diagnose(new SessionLspDiagnoseRequest(serverId: 'php'));
        $arrayResult = $pending->diagnose(['serverId' => 'php']);

        expect($typedResult)->toBeInstanceOf(SessionLspDiagnoseResult::class)
            ->and($typedResult->status)->toBe('started')
            ->and($typedResult->immediateExit?->code)->toBe(1)
            ->and($arrayResult->pid)->toBeNull()
            ->and($arrayResult->immediateExit)->toBeNull();
    });

    it('persists language-server state from typed and array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.lsp.set', [
                'serverId' => 'php',
                'enabled' => false,
                'sessionId' => 'session-123',
            ])
            ->andReturnNull();

        $pending = new PendingLsp($client, 'session-123');
        $pending->set(new SessionLspSetRequest(serverId: 'php', enabled: false));
        $pending->set(['serverId' => 'php', 'enabled' => false]);
    });
});
