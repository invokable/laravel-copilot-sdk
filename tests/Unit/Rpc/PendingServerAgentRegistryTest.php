<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerAgentRegistry;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\AgentRegistryEnsureDirRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryEnsureDirResult;
use Revolution\Copilot\Types\Rpc\AgentRegistryListEntriesResult;
use Revolution\Copilot\Types\Rpc\AgentRegistryRemoveEntryRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryRemoveEntryResult;

describe('PendingServerAgentRegistry', function () {
    it('creates the registry directory with the supplied home paths', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('agentRegistry.ensureDir', [
                'copilotHome' => '/home/alex/.copilot',
                'homeDir' => '/home/alex',
            ])
            ->andReturn(['path' => '/home/alex/.copilot/agents']);

        $registry = (new ServerRpc($client))->agentRegistry();
        $result = $registry->ensureDir(new AgentRegistryEnsureDirRequest(
            homeDir: '/home/alex',
            copilotHome: '/home/alex/.copilot',
        ));

        expect($registry)->toBeInstanceOf(PendingServerAgentRegistry::class)
            ->and($result)->toBeInstanceOf(AgentRegistryEnsureDirResult::class)
            ->and($result->path)->toBe('/home/alex/.copilot/agents');
    });

    it('lists live registry entries with the discovery options', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('agentRegistry.listEntries', [
                'copilotHome' => '/home/alex/.copilot',
                'homeDir' => '/home/alex',
                'excludePid' => 42,
                'staleMs' => 60000,
                'nowMs' => 1000,
                'pidAliveOverride' => false,
            ])
            ->andReturn([
                'entries' => [[
                    'schemaVersion' => 2,
                    'kind' => 'managed-server',
                    'pid' => 42,
                    'host' => '127.0.0.1',
                    'port' => 43210,
                    'startedAt' => '2026-10-31T12:00:00Z',
                    'copilotVersion' => '1.0.106',
                    'lastSeenMs' => 1000,
                    'token' => 'must-not-be-exposed',
                ]],
            ]);

        $result = (new ServerRpc($client))->agentRegistry()->listEntries([
            'homeDir' => '/home/alex',
            'copilotHome' => '/home/alex/.copilot',
            'excludePid' => 42,
            'staleMs' => 60000,
            'nowMs' => 1000,
            'pidAliveOverride' => false,
        ]);

        expect($result)->toBeInstanceOf(AgentRegistryListEntriesResult::class)
            ->and($result->entries[0]->pid)->toBe(42)
            ->and($result->entries[0]->toArray())->not->toHaveKey('token');
    });

    it('removes a local registry entry by process identifier', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('agentRegistry.removeEntry', [
                'copilotHome' => '/home/alex/.copilot',
                'homeDir' => '/home/alex',
                'pid' => 42,
            ])
            ->andReturn(['error' => ['code' => 'ENOENT', 'path' => '/home/alex/.copilot/agents']]);

        $result = (new ServerRpc($client))->agentRegistry()->removeEntry(new AgentRegistryRemoveEntryRequest(
            homeDir: '/home/alex',
            pid: 42,
            copilotHome: '/home/alex/.copilot',
        ));

        expect($result)->toBeInstanceOf(AgentRegistryRemoveEntryResult::class)
            ->and($result->error?->code)->toBe('ENOENT')
            ->and($result->error?->path)->toBe('/home/alex/.copilot/agents');
    });
});
