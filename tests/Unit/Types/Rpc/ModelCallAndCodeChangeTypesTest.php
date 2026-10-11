<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AiCreditsStatus;
use Revolution\Copilot\Enums\ModelCallRequestBodyEncoding;
use Revolution\Copilot\Enums\ModelCallWebSocketFallbackErrorKind;
use Revolution\Copilot\Types\Rpc\ModelMetric;
use Revolution\Copilot\Types\Rpc\ModelMetricRequests;
use Revolution\Copilot\Types\Rpc\ModelMetricUsage;
use Revolution\Copilot\Types\Rpc\QuotaWarningProjection;
use Revolution\Copilot\Types\Rpc\UsageSetCodeChangesRequest;

it('serializes new model-call enums and host code-change updates', function () {
    $metric = new ModelMetric(
        requests: new ModelMetricRequests(count: 1, cost: 0),
        usage: new ModelMetricUsage(inputTokens: 1, outputTokens: 2, cacheReadTokens: 0, cacheWriteTokens: 0),
        aiCreditsStatus: AiCreditsStatus::COMPLETE,
    );
    $warning = QuotaWarningProjection::fromArray([
        'warningType' => 'quota-low',
        'message' => 'Quota is low',
    ]);

    expect(ModelCallRequestBodyEncoding::ZSTD->value)->toBe('zstd')
        ->and(ModelCallWebSocketFallbackErrorKind::CONNECTION_RESET->value)->toBe('connection_reset')
        ->and($metric->toArray()['aiCreditsStatus'])->toBe('complete')
        ->and($warning->toArray())->not->toHaveKey('url')
        ->and((new UsageSetCodeChangesRequest(10, 2, 4))->toArray())->toBe([
            'linesAdded' => 10,
            'linesRemoved' => 2,
            'filesCount' => 4,
        ]);
});
