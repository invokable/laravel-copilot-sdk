<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\CodeChanges;
use Revolution\Copilot\Types\Rpc\ModelMetric;
use Revolution\Copilot\Types\Rpc\ModelMetricRequests;
use Revolution\Copilot\Types\Rpc\ModelMetricUsage;
use Revolution\Copilot\Types\Rpc\SessionUsageGetMetricsResult;

describe('CodeChanges', function () {
    it('can be created from array', function () {
        $changes = CodeChanges::fromArray([
            'linesAdded' => 150,
            'linesRemoved' => 30,
            'filesModifiedCount' => 8,
        ]);

        expect($changes->linesAdded)->toBe(150)
            ->and($changes->linesRemoved)->toBe(30)
            ->and($changes->filesModifiedCount)->toBe(8);
    });

    it('converts to array', function () {
        $changes = new CodeChanges(linesAdded: 10, linesRemoved: 5, filesModifiedCount: 3);

        expect($changes->toArray())->toBe([
            'linesAdded' => 10,
            'linesRemoved' => 5,
            'filesModifiedCount' => 3,
        ]);
    });

    it('implements Arrayable interface', function () {
        expect(new CodeChanges(0, 0, 0))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ModelMetricRequests', function () {
    it('can be created from array', function () {
        $requests = ModelMetricRequests::fromArray([
            'count' => 12,
            'cost' => 3.5,
        ]);

        expect($requests->count)->toBe(12)
            ->and($requests->cost)->toBe(3.5);
    });

    it('converts to array', function () {
        $requests = new ModelMetricRequests(count: 5, cost: 1.0);

        expect($requests->toArray())->toBe([
            'count' => 5,
            'cost' => 1.0,
        ]);
    });

    it('implements Arrayable interface', function () {
        expect(new ModelMetricRequests(0, 0.0))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ModelMetricUsage', function () {
    it('can be created from array', function () {
        $usage = ModelMetricUsage::fromArray([
            'inputTokens' => 5000,
            'outputTokens' => 2000,
            'cacheReadTokens' => 1000,
            'cacheWriteTokens' => 500,
        ]);

        expect($usage->inputTokens)->toBe(5000)
            ->and($usage->outputTokens)->toBe(2000)
            ->and($usage->cacheReadTokens)->toBe(1000)
            ->and($usage->cacheWriteTokens)->toBe(500)
            ->and($usage->reasoningTokens)->toBeNull();
    });

    it('can be created from array with reasoningTokens', function () {
        $usage = ModelMetricUsage::fromArray([
            'inputTokens' => 5000,
            'outputTokens' => 2000,
            'cacheReadTokens' => 1000,
            'cacheWriteTokens' => 500,
            'reasoningTokens' => 800,
        ]);

        expect($usage->inputTokens)->toBe(5000)
            ->and($usage->outputTokens)->toBe(2000)
            ->and($usage->reasoningTokens)->toBe(800);
    });

    it('converts to array', function () {
        $usage = new ModelMetricUsage(
            inputTokens: 100,
            outputTokens: 50,
            cacheReadTokens: 10,
            cacheWriteTokens: 5,
        );

        expect($usage->toArray())->toBe([
            'inputTokens' => 100,
            'outputTokens' => 50,
            'cacheReadTokens' => 10,
            'cacheWriteTokens' => 5,
        ]);
    });

    it('includes reasoningTokens in toArray when present', function () {
        $usage = new ModelMetricUsage(
            inputTokens: 100,
            outputTokens: 50,
            cacheReadTokens: 10,
            cacheWriteTokens: 5,
            reasoningTokens: 25,
        );

        expect($usage->toArray())->toBe([
            'inputTokens' => 100,
            'outputTokens' => 50,
            'cacheReadTokens' => 10,
            'cacheWriteTokens' => 5,
            'reasoningTokens' => 25,
        ]);
    });

    it('implements Arrayable interface', function () {
        expect(new ModelMetricUsage(0, 0, 0, 0))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ModelMetric', function () {
    it('can be created from array', function () {
        $metric = ModelMetric::fromArray([
            'requests' => ['count' => 3, 'cost' => 1.5],
            'usage' => ['inputTokens' => 1000, 'outputTokens' => 500, 'cacheReadTokens' => 100, 'cacheWriteTokens' => 50],
        ]);

        expect($metric->requests)->toBeInstanceOf(ModelMetricRequests::class)
            ->and($metric->requests->count)->toBe(3)
            ->and($metric->usage)->toBeInstanceOf(ModelMetricUsage::class)
            ->and($metric->usage->inputTokens)->toBe(1000);
    });

    it('converts to array', function () {
        $metric = new ModelMetric(
            requests: new ModelMetricRequests(count: 2, cost: 1.0),
            usage: new ModelMetricUsage(inputTokens: 500, outputTokens: 200, cacheReadTokens: 0, cacheWriteTokens: 0),
        );

        $array = $metric->toArray();

        expect($array)->toHaveKey('requests')
            ->and($array)->toHaveKey('usage')
            ->and($array['requests']['count'])->toBe(2)
            ->and($array['usage']['inputTokens'])->toBe(500);
    });

    it('implements Arrayable interface', function () {
        $metric = new ModelMetric(
            requests: new ModelMetricRequests(0, 0.0),
            usage: new ModelMetricUsage(0, 0, 0, 0),
        );
        expect($metric)->toBeInstanceOf(Arrayable::class);
    });
});

describe('SessionUsageGetMetricsResult', function () {
    it('can be created from array with all fields', function () {
        $result = SessionUsageGetMetricsResult::fromArray([
            'totalPremiumRequestCost' => 5.5,
            'totalUserRequests' => 10,
            'totalApiDurationMs' => 12345.6,
            'sessionStartTime' => 1700000000000,
            'codeChanges' => [
                'linesAdded' => 100,
                'linesRemoved' => 20,
                'filesModifiedCount' => 5,
            ],
            'modelMetrics' => [
                'gpt-5' => [
                    'requests' => ['count' => 8, 'cost' => 4.0],
                    'usage' => ['inputTokens' => 3000, 'outputTokens' => 1500, 'cacheReadTokens' => 500, 'cacheWriteTokens' => 200],
                ],
            ],
            'lastCallInputTokens' => 800,
            'lastCallOutputTokens' => 400,
            'currentModel' => 'gpt-5',
        ]);

        expect($result->totalPremiumRequestCost)->toBe(5.5)
            ->and($result->totalUserRequests)->toBe(10)
            ->and($result->totalApiDurationMs)->toBe(12345.6)
            ->and($result->sessionStartTime)->toBe(1700000000000)
            ->and($result->codeChanges)->toBeInstanceOf(CodeChanges::class)
            ->and($result->codeChanges->linesAdded)->toBe(100)
            ->and($result->modelMetrics)->toHaveKey('gpt-5')
            ->and($result->modelMetrics['gpt-5'])->toBeInstanceOf(ModelMetric::class)
            ->and($result->lastCallInputTokens)->toBe(800)
            ->and($result->lastCallOutputTokens)->toBe(400)
            ->and($result->currentModel)->toBe('gpt-5');
    });

    it('can be created without optional currentModel', function () {
        $result = SessionUsageGetMetricsResult::fromArray([
            'totalPremiumRequestCost' => 0,
            'totalUserRequests' => 0,
            'totalApiDurationMs' => 0,
            'sessionStartTime' => 1700000000000,
            'codeChanges' => ['linesAdded' => 0, 'linesRemoved' => 0, 'filesModifiedCount' => 0],
            'modelMetrics' => [],
            'lastCallInputTokens' => 0,
            'lastCallOutputTokens' => 0,
        ]);

        expect($result->currentModel)->toBeNull()
            ->and($result->modelMetrics)->toBe([]);
    });

    it('converts to array roundtrip', function () {
        $data = [
            'totalPremiumRequestCost' => 2.0,
            'totalUserRequests' => 3,
            'totalApiDurationMs' => 1000.0,
            'sessionStartTime' => 1700000000000,
            'codeChanges' => ['linesAdded' => 10, 'linesRemoved' => 5, 'filesModifiedCount' => 2],
            'modelMetrics' => [
                'claude-sonnet-4' => [
                    'requests' => ['count' => 3, 'cost' => 2.0],
                    'usage' => ['inputTokens' => 1000, 'outputTokens' => 500, 'cacheReadTokens' => 0, 'cacheWriteTokens' => 0],
                ],
            ],
            'lastCallInputTokens' => 500,
            'lastCallOutputTokens' => 250,
            'currentModel' => 'claude-sonnet-4',
        ];

        $result = SessionUsageGetMetricsResult::fromArray($data);
        $array = $result->toArray();

        expect($array['totalPremiumRequestCost'])->toBe(2.0)
            ->and($array['totalUserRequests'])->toBe(3)
            ->and($array['modelMetrics'])->toHaveKey('claude-sonnet-4')
            ->and($array['currentModel'])->toBe('claude-sonnet-4');
    });

    it('excludes null currentModel from toArray', function () {
        $result = SessionUsageGetMetricsResult::fromArray([
            'totalPremiumRequestCost' => 0,
            'totalUserRequests' => 0,
            'totalApiDurationMs' => 0,
            'sessionStartTime' => 1700000000000,
            'codeChanges' => ['linesAdded' => 0, 'linesRemoved' => 0, 'filesModifiedCount' => 0],
            'modelMetrics' => [],
            'lastCallInputTokens' => 0,
            'lastCallOutputTokens' => 0,
        ]);

        expect($result->toArray())->not->toHaveKey('currentModel');
    });

    it('implements Arrayable interface', function () {
        $result = SessionUsageGetMetricsResult::fromArray([
            'totalPremiumRequestCost' => 0,
            'totalUserRequests' => 0,
            'totalApiDurationMs' => 0,
            'sessionStartTime' => 0,
            'codeChanges' => ['linesAdded' => 0, 'linesRemoved' => 0, 'filesModifiedCount' => 0],
            'modelMetrics' => [],
            'lastCallInputTokens' => 0,
            'lastCallOutputTokens' => 0,
        ]);
        expect($result)->toBeInstanceOf(Arrayable::class);
    });
});
