<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessionDynamicContext;
use Revolution\Copilot\Types\Rpc\SessionDynamicContextConfig;
use Revolution\Copilot\Types\Rpc\SessionDynamicContextGetConfigResponse;
use Revolution\Copilot\Types\Rpc\SessionDynamicContextSetConfigRequest;

describe('PendingSessionDynamicContext', function () {
    it('returns the session dynamic-context configuration', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.dynamicContext.getConfig', [
                'sessionId' => 'session-1',
            ])
            ->andReturn([
                'config' => [
                    'storePath' => '/tmp/dynamic-context',
                    'repository' => 'owner/repo',
                    'branch' => 'main',
                ],
            ]);

        $response = (new PendingSessionDynamicContext($client, 'session-1'))->getConfig();

        expect($response)->toBeInstanceOf(SessionDynamicContextGetConfigResponse::class)
            ->and($response->config)->toBeInstanceOf(SessionDynamicContextConfig::class)
            ->and($response->config->toArray())->toBe([
                'storePath' => '/tmp/dynamic-context',
                'repository' => 'owner/repo',
                'branch' => 'main',
            ]);
    });

    it('returns null when the session dynamic-context configuration is unset', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.dynamicContext.getConfig', [
                'sessionId' => 'session-1',
            ])
            ->andReturn(['config' => null]);

        $response = (new PendingSessionDynamicContext($client, 'session-1'))->getConfig();

        expect($response->config)->toBeNull()
            ->and($response->toArray())->toBe(['config' => null]);
    });

    it('sends the dynamic-context configuration with the session ID', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.dynamicContext.setConfig', [
                'storePath' => '/tmp/dynamic-context',
                'repository' => 'owner/repo',
                'branch' => 'main',
                'sessionId' => 'session-1',
            ])
            ->andReturn(null);

        (new PendingSessionDynamicContext($client, 'session-1'))->setConfig([
            'storePath' => '/tmp/dynamic-context',
            'repository' => 'owner/repo',
            'branch' => 'main',
            'sessionId' => 'untrusted-session',
        ]);
    });

    it('sends the dynamic-context configuration from its request DTO', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.dynamicContext.setConfig', [
                'storePath' => '/tmp/dynamic-context',
                'repository' => 'owner/repo',
                'branch' => 'main',
                'sessionId' => 'session-1',
            ])
            ->andReturn(null);

        (new PendingSessionDynamicContext($client, 'session-1'))->setConfig(
            new SessionDynamicContextSetConfigRequest(
                storePath: '/tmp/dynamic-context',
                repository: 'owner/repo',
                branch: 'main',
            ),
        );
    });
});
