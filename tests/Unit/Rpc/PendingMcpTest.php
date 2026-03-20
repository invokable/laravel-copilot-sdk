<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Types\Rpc\SessionMcpDisableParams;
use Revolution\Copilot\Types\Rpc\SessionMcpEnableParams;
use Revolution\Copilot\Types\Rpc\SessionMcpListResult;

describe('PendingMcp', function () {
    it('calls session.mcp.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.list', ['sessionId' => 'session-abc'])
            ->andReturn([
                'servers' => [
                    [
                        'name' => 'github',
                        'status' => 'connected',
                        'source' => 'workspace',
                    ],
                ],
            ]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(SessionMcpListResult::class)
            ->and($result->servers)->toHaveCount(1)
            ->and($result->servers[0]->name)->toBe('github');
    });

    it('calls session.mcp.list with empty result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.list', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(SessionMcpListResult::class)
            ->and($result->servers)->toBe([]);
    });

    it('calls session.mcp.enable with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'github'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->enable(new SessionMcpEnableParams(serverName: 'github'));

        expect($result)->toBe([]);
    });

    it('calls session.mcp.enable with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'slack'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->enable(['serverName' => 'slack']);

        expect($result)->toBe([]);
    });

    it('calls session.mcp.disable with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'github'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->disable(new SessionMcpDisableParams(serverName: 'github'));

        expect($result)->toBe([]);
    });

    it('calls session.mcp.disable with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'slack'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->disable(['serverName' => 'slack']);

        expect($result)->toBe([]);
    });

    it('calls session.mcp.reload', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.reload', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->reload();

        expect($result)->toBe([]);
    });
});
