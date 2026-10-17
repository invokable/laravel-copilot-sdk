<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogSelectionDecision;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCatalog;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogSearchRequest;
use Revolution\Copilot\Types\Rpc\CatalogSelectionRequest;
use Revolution\Copilot\Types\Rpc\CatalogSelectionResult;
use Revolution\Copilot\Types\Rpc\CatalogSelectionSelected;

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
