<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingUsage;
use Revolution\Copilot\Types\Rpc\SessionUsageGetMetricsResult;

describe('PendingUsage', function () {
    it('calls session.usage.getMetrics and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.usage.getMetrics',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'),
            )
            ->andReturn([
                'totalPremiumRequestCost' => 3.5,
                'totalUserRequests' => 7,
                'totalApiDurationMs' => 5000.0,
                'sessionStartTime' => 1700000000000,
                'codeChanges' => [
                    'linesAdded' => 50,
                    'linesRemoved' => 10,
                    'filesModifiedCount' => 4,
                ],
                'modelMetrics' => [
                    'gpt-5' => [
                        'requests' => ['count' => 5, 'cost' => 2.5],
                        'usage' => [
                            'inputTokens' => 2000,
                            'outputTokens' => 1000,
                            'cacheReadTokens' => 300,
                            'cacheWriteTokens' => 100,
                        ],
                    ],
                ],
                'lastCallInputTokens' => 600,
                'lastCallOutputTokens' => 300,
                'currentModel' => 'gpt-5',
            ]);

        $pending = new PendingUsage($client, 'session-abc');
        $result = $pending->getMetrics();

        expect($result)->toBeInstanceOf(SessionUsageGetMetricsResult::class)
            ->and($result->totalPremiumRequestCost)->toBe(3.5)
            ->and($result->totalUserRequests)->toBe(7)
            ->and($result->codeChanges->linesAdded)->toBe(50)
            ->and($result->modelMetrics)->toHaveKey('gpt-5')
            ->and($result->currentModel)->toBe('gpt-5');
    });
});
