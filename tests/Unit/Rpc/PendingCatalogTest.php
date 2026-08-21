<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCatalog;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogSearchRequest;

describe('PendingCatalog', function () {
    it('calls catalog.search with typed params and returns raw array', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'catalog.search',
                [
                    'contract' => ['protocolVersion' => 1, 'requiredCapabilities' => ['mcp-server-card']],
                    'query' => 'github',
                ],
            )
            ->andReturn(['kind' => 'succeeded', 'searchId' => 'sid1', 'candidates' => [], 'truncated' => false, 'negotiated' => ['runtimeProtocolVersion' => 1, 'grantedCapabilities' => []]]);

        $pending = new PendingCatalog($client);
        $result = $pending->search(new CatalogSearchRequest(
            contract: new CatalogClientContract(1, ['mcp-server-card']),
            query: 'github',
        ));

        expect($result)->toBeArray()
            ->and($result['kind'])->toBe('succeeded')
            ->and($result['searchId'])->toBe('sid1');
    });

    it('calls catalog.search with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'catalog.search',
                [
                    'contract' => ['protocolVersion' => 2, 'requiredCapabilities' => []],
                    'query' => 'stripe',
                    'limit' => 5,
                    'kinds' => ['mcp-server'],
                ],
            )
            ->andReturn(['kind' => 'unavailable', 'reason' => 'search-unavailable', 'message' => 'Search is not available.']);

        $pending = new PendingCatalog($client);
        $result = $pending->search([
            'contract' => ['protocolVersion' => 2, 'requiredCapabilities' => []],
            'query' => 'stripe',
            'limit' => 5,
            'kinds' => ['mcp-server'],
        ]);

        expect($result)->toBeArray()
            ->and($result['kind'])->toBe('unavailable');
    });
});
