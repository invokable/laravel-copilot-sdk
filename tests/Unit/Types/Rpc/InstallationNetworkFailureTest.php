<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogNetworkFailureReason;
use Revolution\Copilot\Types\Rpc\CatalogReferenceInstallationResult;
use Revolution\Copilot\Types\Rpc\PluginInstallationManagementResult;
use Revolution\Copilot\Types\Rpc\PluginInstallationResult;
use Revolution\Copilot\Types\Rpc\SkillInstallationManagementResult;

it('decodes catalog network failures from installation results', function () {
    $response = [
        'kind' => 'network-failure',
        'reason' => 'rate-limited',
        'statusCode' => 429,
        'retryAfterSeconds' => 30,
        'message' => 'Retry later',
    ];
    $expected = [
        'kind' => 'network-failure',
        'message' => 'Retry later',
        'reason' => 'rate-limited',
        'statusCode' => 429,
        'retryAfterSeconds' => 30,
    ];
    $results = [
        CatalogReferenceInstallationResult::fromArray($response),
        PluginInstallationManagementResult::fromArray($response),
        PluginInstallationResult::fromArray($response),
        SkillInstallationManagementResult::fromArray($response),
    ];

    foreach ($results as $result) {
        expect($result->reason)->toBe(CatalogNetworkFailureReason::RateLimited)
            ->and($result->statusCode)->toBe(429)
            ->and($result->retryAfterSeconds)->toBe(30)
            ->and($result->toArray())->toBe($expected);
    }
});
