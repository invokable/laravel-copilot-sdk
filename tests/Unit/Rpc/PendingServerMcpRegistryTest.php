<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerMcpConfig;
use Revolution\Copilot\Rpc\PendingServerMcpRegistry;
use Revolution\Copilot\Types\Rpc\McpRegistryCancelRequest;
use Revolution\Copilot\Types\Rpc\McpRegistryCancelResult;
use Revolution\Copilot\Types\Rpc\McpRegistryRequestIdResult;
use Revolution\Copilot\Types\Rpc\McpRegistrySearchRequest;
use Revolution\Copilot\Types\Rpc\McpRegistrySearchResult;

describe('PendingServerMcpRegistry', function () {
    it('is available from the server MCP RPC group', function () {
        $client = Mockery::mock(JsonRpcClient::class);

        expect((new PendingServerMcpConfig($client))->registry())
            ->toBeInstanceOf(PendingServerMcpRegistry::class);
    });

    it('allocates a cancellable search ID', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.registry.allocateRequestId', [])
            ->andReturn(['requestId' => 42]);

        $result = (new PendingServerMcpRegistry($client))->allocateRequestId();

        expect($result)->toBeInstanceOf(McpRegistryRequestIdResult::class)
            ->and($result->requestId)->toBe(42);
    });

    it('searches the registry with typed params and preserves opaque servers', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.registry.search', [
                'requestId' => 42,
                'authInfo' => ['kind' => 'gh-cli'],
                'query' => 'database',
                'repository' => 'owner/project',
                'limit' => 10,
            ])
            ->andReturn(['servers' => [['name' => 'docs']]]);

        $result = (new PendingServerMcpRegistry($client))->search(new McpRegistrySearchRequest(
            requestId: 42,
            authInfo: ['kind' => 'gh-cli'],
            limit: 10,
            query: 'database',
            repository: 'owner/project',
        ));

        expect($result)->toBeInstanceOf(McpRegistrySearchResult::class)
            ->and($result->servers)->toBe([['name' => 'docs']]);
    });

    it('searches the registry with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.registry.search', [
                'requestId' => 7,
                'authInfo' => ['kind' => 'token'],
                'limit' => 3,
            ])
            ->andReturn(['servers' => []]);

        $result = (new PendingServerMcpRegistry($client))->search([
            'requestId' => 7,
            'authInfo' => ['kind' => 'token'],
            'limit' => 3,
        ]);

        expect($result->servers)->toBe([]);
    });

    it('cancels a registry search and returns the cancellation result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.registry.cancel', ['requestId' => 42])
            ->andReturn(['canceled' => true]);

        $result = (new PendingServerMcpRegistry($client))->cancel(new McpRegistryCancelRequest(requestId: 42));

        expect($result)->toBeInstanceOf(McpRegistryCancelResult::class)
            ->and($result->canceled)->toBeTrue();
    });

    it('cancels a registry search with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('mcp.registry.cancel', ['requestId' => 99])
            ->andReturn(['canceled' => false]);

        $result = (new PendingServerMcpRegistry($client))->cancel(['requestId' => 99]);

        expect($result->canceled)->toBeFalse();
    });
});
