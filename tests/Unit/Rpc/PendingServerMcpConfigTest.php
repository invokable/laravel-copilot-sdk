<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerMcpConfig;
use Revolution\Copilot\Types\Rpc\McpConfigAddParams;
use Revolution\Copilot\Types\Rpc\McpConfigListResult;
use Revolution\Copilot\Types\Rpc\McpConfigRemoveParams;
use Revolution\Copilot\Types\Rpc\McpConfigUpdateParams;
use Revolution\Copilot\Types\Rpc\McpDiscoverParams;
use Revolution\Copilot\Types\Rpc\McpDiscoverResult;
use Revolution\Copilot\Types\Rpc\McpServerValue;

describe('PendingServerMcpConfig', function () {
    it('calls mcp.config.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.config.list', [])
            ->andReturn([
                'servers' => [
                    'github' => [
                        'type' => 'local',
                        'command' => 'gh',
                        'args' => ['mcp'],
                    ],
                ],
            ]);

        $pending = new PendingServerMcpConfig($client);
        $result = $pending->list();

        expect($result)->toBeInstanceOf(McpConfigListResult::class)
            ->and($result->servers)->toHaveCount(1)
            ->and($result->servers['github']->command)->toBe('gh');
    });

    it('calls mcp.config.list with empty result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.config.list', [])
            ->andReturn([]);

        $pending = new PendingServerMcpConfig($client);
        $result = $pending->list();

        expect($result)->toBeInstanceOf(McpConfigListResult::class)
            ->and($result->servers)->toBe([]);
    });

    it('calls mcp.config.add with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'mcp.config.add',
                Mockery::on(fn ($params) => $params['name'] === 'my-server'
                    && $params['config']['type'] === 'local'
                    && $params['config']['command'] === 'php'),
            )
            ->andReturn([]);

        $pending = new PendingServerMcpConfig($client);
        $pending->add(new McpConfigAddParams(
            name: 'my-server',
            config: new McpServerValue(type: 'local', command: 'php', args: ['artisan', 'mcp']),
        ));
    });

    it('calls mcp.config.add with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'mcp.config.add',
                Mockery::on(fn ($params) => $params['name'] === 'remote-server'
                    && $params['config']['type'] === 'http'),
            )
            ->andReturn([]);

        $pending = new PendingServerMcpConfig($client);
        $pending->add(['name' => 'remote-server', 'config' => ['type' => 'http', 'url' => 'https://example.com']]);
    });

    it('calls mcp.config.update with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'mcp.config.update',
                Mockery::on(fn ($params) => $params['name'] === 'my-server'
                    && $params['config']['url'] === 'https://new-url.com'),
            )
            ->andReturn([]);

        $pending = new PendingServerMcpConfig($client);
        $pending->update(new McpConfigUpdateParams(
            name: 'my-server',
            config: new McpServerValue(type: 'http', url: 'https://new-url.com'),
        ));
    });

    it('calls mcp.config.remove with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'mcp.config.remove',
                Mockery::on(fn ($params) => $params['name'] === 'old-server'),
            )
            ->andReturn([]);

        $pending = new PendingServerMcpConfig($client);
        $pending->remove(new McpConfigRemoveParams(name: 'old-server'));
    });

    it('calls mcp.config.remove with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'mcp.config.remove',
                Mockery::on(fn ($params) => $params['name'] === 'old-server'),
            )
            ->andReturn([]);

        $pending = new PendingServerMcpConfig($client);
        $pending->remove(['name' => 'old-server']);
    });

    it('calls mcp.discover and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'mcp.discover',
                Mockery::on(fn ($params) => $params['workingDirectory'] === '/workspace'),
            )
            ->andReturn([
                'servers' => [
                    [
                        'name' => 'github',
                        'type' => 'local',
                        'source' => 'user',
                        'enabled' => true,
                    ],
                    [
                        'name' => 'custom',
                        'source' => 'workspace',
                        'enabled' => false,
                    ],
                ],
            ]);

        $pending = new PendingServerMcpConfig($client);
        $result = $pending->discover(new McpDiscoverParams(workingDirectory: '/workspace'));

        expect($result)->toBeInstanceOf(McpDiscoverResult::class)
            ->and($result->servers)->toHaveCount(2)
            ->and($result->servers[0]->name)->toBe('github')
            ->and($result->servers[1]->enabled)->toBeFalse();
    });

    it('calls mcp.discover with empty params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.discover', [])
            ->andReturn(['servers' => []]);

        $pending = new PendingServerMcpConfig($client);
        $result = $pending->discover();

        expect($result)->toBeInstanceOf(McpDiscoverResult::class)
            ->and($result->servers)->toBe([]);
    });

    it('calls mcp.discover with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'mcp.discover',
                Mockery::on(fn ($params) => $params['workingDirectory'] === '/project'),
            )
            ->andReturn(['servers' => []]);

        $pending = new PendingServerMcpConfig($client);
        $result = $pending->discover(['workingDirectory' => '/project']);

        expect($result)->toBeInstanceOf(McpDiscoverResult::class);
    });
});
