<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerMcpConfig;
use Revolution\Copilot\Rpc\PendingServerMcpOauth;
use Revolution\Copilot\Types\Rpc\McpOauthCheckDcrSupportRequest;
use Revolution\Copilot\Types\Rpc\McpOauthCheckDcrSupportResult;
use Revolution\Copilot\Types\Rpc\McpOauthIsMicrosoftAuthorizationServerRequest;
use Revolution\Copilot\Types\Rpc\McpOauthIsMicrosoftAuthorizationServerResult;

describe('PendingServerMcpOauth', function () {
    it('checks DCR support through the server-scoped MCP OAuth namespace', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.oauth.checkDcrSupport', [
                'serverUrl' => 'https://mcp.example.com',
                'resourceMetadataText' => '{"authorization_servers":["https://auth.example.com"]}',
                'sandboxProxy' => 'https://proxy.example.com',
                'sessionId' => 'session-1',
            ])
            ->andReturn([
                'supportsDcr' => true,
                'authorizationServerUrl' => 'https://auth.example.com',
            ]);

        $oauth = (new PendingServerMcpConfig($client))->oauth();
        $result = $oauth->checkDcrSupport(new McpOauthCheckDcrSupportRequest(
            serverUrl: 'https://mcp.example.com',
            resourceMetadataText: '{"authorization_servers":["https://auth.example.com"]}',
            sandboxProxy: 'https://proxy.example.com',
            sessionId: 'session-1',
        ));

        expect($oauth)
            ->toBeInstanceOf(PendingServerMcpOauth::class)
            ->and($result)
            ->toBeInstanceOf(McpOauthCheckDcrSupportResult::class)
            ->and($result->supportsDcr)->toBeTrue()
            ->and($result->authorizationServerUrl)->toBe('https://auth.example.com');
    });

    it('classifies a Microsoft authorization server from array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.oauth.isMicrosoftAuthorizationServer', [
                'authorizationServerUrl' => 'https://login.microsoftonline.com/tenant/v2.0',
            ])
            ->andReturn(['isMicrosoftAuthorizationServer' => true]);

        $result = (new PendingServerMcpConfig($client))->oauth()->isMicrosoftAuthorizationServer([
            'authorizationServerUrl' => 'https://login.microsoftonline.com/tenant/v2.0',
        ]);

        expect($result)
            ->toBeInstanceOf(McpOauthIsMicrosoftAuthorizationServerResult::class)
            ->and($result->isMicrosoftAuthorizationServer)->toBeTrue();
    });

    it('serializes the authorization-server request DTO', function () {
        $request = new McpOauthIsMicrosoftAuthorizationServerRequest(
            'https://login.microsoftonline.com/tenant/v2.0',
        );

        expect($request->toArray())->toBe([
            'authorizationServerUrl' => 'https://login.microsoftonline.com/tenant/v2.0',
        ]);
    });
});
