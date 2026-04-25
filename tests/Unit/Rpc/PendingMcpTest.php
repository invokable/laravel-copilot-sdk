<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Types\Rpc\McpDisableRequest;
use Revolution\Copilot\Types\Rpc\McpEnableRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginResult;
use Revolution\Copilot\Types\Rpc\McpServerList;

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

        expect($result)->toBeInstanceOf(McpServerList::class)
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

        expect($result)->toBeInstanceOf(McpServerList::class)
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
        $result = $pending->enable(new McpEnableRequest(serverName: 'github'));

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
        $result = $pending->disable(new McpDisableRequest(serverName: 'github'));

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

    it('calls session.mcp.oauth.login with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.oauth.login',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'my-mcp-server'),
            )
            ->andReturn(['authorizationUrl' => 'https://github.com/login/oauth/authorize?client_id=abc']);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->login(new McpOauthLoginRequest(serverName: 'my-mcp-server'));

        expect($result)->toBeInstanceOf(McpOauthLoginResult::class)
            ->and($result->authorizationUrl)->toContain('github.com');
    });

    it('calls session.mcp.oauth.login with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.oauth.login',
                Mockery::on(fn ($params) => $params['serverName'] === 'remote-server'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->login(['serverName' => 'remote-server']);

        expect($result)->toBeInstanceOf(McpOauthLoginResult::class)
            ->and($result->authorizationUrl)->toBeNull();
    });
});
