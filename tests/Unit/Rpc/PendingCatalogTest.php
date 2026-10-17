<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogSelectionDecision;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerCatalog;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogGetGeneratedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogGetSavedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogSearchRequest;
use Revolution\Copilot\Types\Rpc\CatalogSelectionRequest;
use Revolution\Copilot\Types\Rpc\CatalogSelectionResult;
use Revolution\Copilot\Types\Rpc\CatalogSelectionSelected;

describe('PendingServerCatalog', function () {
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

        $pending = new PendingServerCatalog($client);
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

        $pending = new PendingServerCatalog($client);
        $result = $pending->search([
            'contract' => ['protocolVersion' => 2, 'requiredCapabilities' => []],
            'query' => 'stripe',
            'limit' => 5,
            'kinds' => ['mcp-server'],
        ]);

        expect($result)->toBeArray()
            ->and($result['kind'])->toBe('unavailable');
    });

    it('forwards categories only with the negotiated category capability', function () {
        $params = [
            'contract' => ['protocolVersion' => 3, 'requiredCapabilities' => ['catalog-search-categories']],
            'query' => 'design tools',
            'categories' => ['design', 'test'],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('catalog.search', $params)
            ->andReturn(['kind' => 'succeeded', 'candidates' => []]);

        $result = (new PendingServerCatalog($client))->search(new CatalogSearchRequest(
            contract: new CatalogClientContract(3, [CatalogCapability::CatalogSearchCategories->value]),
            query: 'design tools',
            categories: ['design', 'test'],
        ));

        expect($result)->toBe(['kind' => 'succeeded', 'candidates' => []]);
    });

    it('calls saved and generated feed RPCs with fake responses', function () {
        $contractData = [
            'protocolVersion' => 3,
            'requiredCapabilities' => [
                'catalog-feeds',
                'catalog-search-credential-required',
                'catalog-search-session-bound',
            ],
        ];
        $savedParams = [
            'contract' => $contractData,
            'feedId' => 'feed-opaque-id',
            'policySessionId' => 'session-1',
            'trustTiers' => ['T1'],
        ];
        $generatedParams = [
            'contract' => $contractData,
            'strategy' => 'telemetry',
            'key' => 'popular',
        ];
        $savedResponse = [
            'kind' => 'succeeded',
            'searchId' => 'search-1',
            'feed' => ['id' => 'feed-opaque-id', 'name' => 'Saved', 'version' => 1],
            'entries' => [['status' => 'filtered', 'identifier' => 'resource-1']],
            'negotiated' => ['runtimeProtocolVersion' => 3, 'grantedCapabilities' => ['catalog-feeds']],
        ];
        $generatedResponse = [
            'kind' => 'unavailable',
            'reason' => 'feed-not-found',
            'message' => 'Feed is unavailable.',
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('catalog.getSavedFeed', $savedParams)
            ->andReturn($savedResponse);
        $client->shouldReceive('request')
            ->once()
            ->with('catalog.getGeneratedFeed', $generatedParams)
            ->andReturn($generatedResponse);
        $catalog = new PendingServerCatalog($client);

        $saved = $catalog->getSavedFeed(new CatalogGetSavedFeedRequest(
            contract: new CatalogClientContract(3, $contractData['requiredCapabilities']),
            feedId: 'feed-opaque-id',
            policySessionId: 'session-1',
            trustTiers: ['T1'],
        ));
        $generated = $catalog->getGeneratedFeed(new CatalogGetGeneratedFeedRequest(
            contract: new CatalogClientContract(3, $contractData['requiredCapabilities']),
            strategy: 'telemetry',
            key: 'popular',
        ));

        expect($saved)->toBe($savedResponse)
            ->and($saved['entries'][0]['status'])->toBe('filtered')
            ->and($generated)->toBe($generatedResponse);
    });

    it('selects a catalog candidate and forwards its handle to skill planning', function () {
        $contractData = ['protocolVersion' => 3, 'requiredCapabilities' => ['catalog-selection', 'ai-skill-installation']];
        $params = [
            'contract' => $contractData,
            'sessionId' => 'session-1',
            'selectionRef' => 'selection-1',
            'outcome' => 'selected',
        ];
        $selected = ['kind' => 'selected', 'candidateHandle' => 'candidate-1', 'searchId' => 'search-1'];
        $invalid = ['kind' => 'invalid-request', 'message' => 'Unknown selection'];
        $planParams = [
            'contract' => $contractData,
            'candidateHandle' => 'candidate-1',
            'policySessionId' => 'session-1',
        ];
        $planResult = [
            'kind' => 'outcome',
            'outcome' => [
                'kind' => 'install-planned',
                'plan' => ['planHandle' => 'plan-1'],
            ],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('catalog.select', $params)
            ->andReturn($selected, $invalid);
        $client->shouldReceive('request')
            ->once()
            ->with('skills.planInstall', $planParams)
            ->andReturn($planResult);

        $rpc = new ServerRpc($client);
        $selection = $rpc->catalog()->select(new CatalogSelectionRequest(
            contract: new CatalogClientContract(3, ['catalog-selection', 'ai-skill-installation']),
            sessionId: 'session-1',
            selectionRef: 'selection-1',
            outcome: CatalogSelectionDecision::Selected,
        ));
        $invalidSelection = $rpc->catalog()->select($params);
        $plan = $rpc->skills()->planInstall([
            'contract' => $contractData,
            'candidateHandle' => $selection['candidateHandle'],
            'policySessionId' => 'session-1',
        ]);

        expect($selection)->toBe($selected)
            ->and($invalidSelection)->toBe($invalid)
            ->and($plan->kind->value)->toBe('outcome')
            ->and($plan->outcome->kind->value)->toBe('install-planned')
            ->and($plan->outcome->plan)->toBe(['planHandle' => 'plan-1']);
    });

    it('round-trips catalog selection request and result types', function () {
        $contractData = ['protocolVersion' => 3, 'requiredCapabilities' => ['catalog-selection']];
        $params = [
            'contract' => $contractData,
            'sessionId' => 'session-1',
            'selectionRef' => 'selection-1',
            'outcome' => 'timed-out',
        ];
        $nonSelectedResult = [
            'kind' => 'stale',
            'searchId' => 'search-1',
            'message' => 'The selection has expired.',
        ];
        $selectedResult = [
            'kind' => 'selected',
            'candidateHandle' => 'candidate-1',
            'searchId' => 'search-1',
        ];

        expect(CatalogSelectionRequest::fromArray($params)->toArray())->toBe($params)
            ->and((new CatalogSelectionRequest(
                contract: new CatalogClientContract(3, ['catalog-selection']),
                sessionId: 'session-1',
                selectionRef: 'selection-1',
                outcome: CatalogSelectionDecision::TimedOut,
            ))->toArray())->toBe($params)
            ->and(CatalogSelectionResult::fromArray($nonSelectedResult)->toArray())->toBe($nonSelectedResult)
            ->and(CatalogSelectionSelected::fromArray($selectedResult)->toArray())->toBe($selectedResult);
    });
});
