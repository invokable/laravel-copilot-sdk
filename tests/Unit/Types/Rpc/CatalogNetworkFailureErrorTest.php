<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogNetworkFailureReason;
use Revolution\Copilot\Types\Rpc\CatalogNetworkFailureError;

describe('CatalogNetworkFailureError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogNetworkFailureError(
            reason: CatalogNetworkFailureReason::Timeout,
            statusCode: null,
            message: 'Request timed out',
        );

        expect($error->kind)->toBe('network-failure')
            ->and($error->reason)->toBe(CatalogNetworkFailureReason::Timeout)
            ->and($error->statusCode)->toBeNull()
            ->and($error->retryAfterSeconds)->toBeNull();
    });

    it('can be created from array with retryAfterSeconds', function () {
        $error = CatalogNetworkFailureError::fromArray([
            'reason' => 'rate-limited',
            'statusCode' => 429,
            'message' => 'Rate limited',
            'retryAfterSeconds' => 30,
        ]);

        expect($error->reason)->toBe(CatalogNetworkFailureReason::RateLimited)
            ->and($error->statusCode)->toBe(429)
            ->and($error->retryAfterSeconds)->toBe(30);
    });

    it('converts to array including retryAfterSeconds when present', function () {
        $error = new CatalogNetworkFailureError(
            reason: CatalogNetworkFailureReason::ServiceUnavailable,
            statusCode: 503,
            message: 'Service unavailable',
            retryAfterSeconds: 5,
        );

        expect($error->toArray())->toBe([
            'kind' => 'network-failure',
            'reason' => 'service-unavailable',
            'message' => 'Service unavailable',
            'statusCode' => 503,
            'retryAfterSeconds' => 5,
        ]);
    });

    it('omits retryAfterSeconds from array when null', function () {
        $error = new CatalogNetworkFailureError(
            reason: CatalogNetworkFailureReason::Offline,
            statusCode: null,
            message: 'No network',
        );

        expect($error->toArray())->toBe([
            'kind' => 'network-failure',
            'reason' => 'offline',
            'message' => 'No network',
        ]);
    });
});

describe('CatalogNetworkFailureReason', function () {
    it('has new failure reason cases', function () {
        expect(CatalogNetworkFailureReason::ProxyAuthenticationRequired->value)->toBe('proxy-authentication-required')
            ->and(CatalogNetworkFailureReason::RateLimited->value)->toBe('rate-limited')
            ->and(CatalogNetworkFailureReason::ServiceUnavailable->value)->toBe('service-unavailable');
    });

    it('can create new cases from string', function () {
        expect(CatalogNetworkFailureReason::from('proxy-authentication-required'))->toBe(CatalogNetworkFailureReason::ProxyAuthenticationRequired)
            ->and(CatalogNetworkFailureReason::from('rate-limited'))->toBe(CatalogNetworkFailureReason::RateLimited)
            ->and(CatalogNetworkFailureReason::from('service-unavailable'))->toBe(CatalogNetworkFailureReason::ServiceUnavailable);
    });
});
