<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AccountGetQuotaResult;
use Revolution\Copilot\Types\Rpc\QuotaSnapshot;

describe('QuotaSnapshot', function () {
    it('can be created from array', function () {
        $snapshot = QuotaSnapshot::fromArray([
            'entitlementRequests' => 100,
            'usedRequests' => 50,
            'remainingPercentage' => 50.0,
            'overage' => 0,
            'overageAllowedWithExhaustedQuota' => true,
            'resetDate' => '2026-03-01T00:00:00Z',
        ]);

        expect($snapshot->entitlementRequests)->toBe(100)
            ->and($snapshot->usedRequests)->toBe(50)
            ->and($snapshot->remainingPercentage)->toBe(50.0)
            ->and($snapshot->overage)->toBe(0)
            ->and($snapshot->overageAllowedWithExhaustedQuota)->toBeTrue()
            ->and($snapshot->resetDate)->toBe('2026-03-01T00:00:00Z');
    });

    it('can be created without optional fields', function () {
        $snapshot = QuotaSnapshot::fromArray([
            'entitlementRequests' => 100,
            'usedRequests' => 50,
            'remainingPercentage' => 50.0,
            'overage' => 0,
            'overageAllowedWithExhaustedQuota' => false,
        ]);

        expect($snapshot->resetDate)->toBeNull();
    });

    it('filters null values in toArray', function () {
        $snapshot = new QuotaSnapshot(
            entitlementRequests: 100,
            usedRequests: 50,
            remainingPercentage: 50.0,
            overage: 0,
            overageAllowedWithExhaustedQuota: false,
        );

        expect($snapshot->toArray())->not->toHaveKey('resetDate');
    });
});

describe('AccountGetQuotaResult', function () {
    it('can be created from array', function () {
        $result = AccountGetQuotaResult::fromArray([
            'quotaSnapshots' => [
                'chat' => [
                    'entitlementRequests' => 500,
                    'usedRequests' => 100,
                    'remainingPercentage' => 80.0,
                    'overage' => 0,
                    'overageAllowedWithExhaustedQuota' => true,
                ],
            ],
        ]);

        expect($result->quotaSnapshots)->toHaveKey('chat')
            ->and($result->quotaSnapshots['chat'])->toBeInstanceOf(QuotaSnapshot::class)
            ->and($result->quotaSnapshots['chat']->entitlementRequests)->toBe(500);
    });

    it('can convert to array', function () {
        $result = AccountGetQuotaResult::fromArray([
            'quotaSnapshots' => [
                'chat' => [
                    'entitlementRequests' => 500,
                    'usedRequests' => 100,
                    'remainingPercentage' => 80.0,
                    'overage' => 0,
                    'overageAllowedWithExhaustedQuota' => true,
                ],
            ],
        ]);

        $array = $result->toArray();

        expect($array['quotaSnapshots']['chat']['entitlementRequests'])->toBe(500);
    });
});
