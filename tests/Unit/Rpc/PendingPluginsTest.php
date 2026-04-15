<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingPlugins;
use Revolution\Copilot\Types\Rpc\PluginList;

describe('PendingPlugins', function () {
    it('calls session.plugins.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.list', ['sessionId' => 'session-abc'])
            ->andReturn([
                'plugins' => [
                    [
                        'name' => 'eslint',
                        'marketplace' => 'npm',
                        'enabled' => true,
                        'version' => '8.0.0',
                    ],
                ],
            ]);

        $pending = new PendingPlugins($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(PluginList::class)
            ->and($result->plugins)->toHaveCount(1)
            ->and($result->plugins[0]->name)->toBe('eslint');
    });

    it('calls session.plugins.list with empty result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.list', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingPlugins($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(PluginList::class)
            ->and($result->plugins)->toBe([]);
    });
});
