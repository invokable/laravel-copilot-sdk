<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ModelProviderKind;
use Revolution\Copilot\Enums\ProviderMonthlyUsageScope;
use Revolution\Copilot\Enums\ProviderMonthlyUsageState;
use Revolution\Copilot\Enums\ProviderQuotaAccessState;
use Revolution\Copilot\Enums\ProviderQuotaCapacityState;
use Revolution\Copilot\Enums\ProviderQuotaUnit;
use Revolution\Copilot\Enums\SessionQuotaPlanTier;
use Revolution\Copilot\Types\Rpc\ModelProviderRef;
use Revolution\Copilot\Types\Rpc\ProviderMonthlyUsage;
use Revolution\Copilot\Types\Rpc\ProviderQuotaBudgetMetadata;
use Revolution\Copilot\Types\Rpc\ProviderQuotaObservationData;
use Revolution\Copilot\Types\Rpc\ProviderQuotaState;
use Revolution\Copilot\Types\Rpc\SessionQuotaGetResult;

it('preserves provider quota state, including zero and explicitly-null measurements', function () {
    $quota = ProviderQuotaState::fromArray([
        'accessState' => 'allowed',
        'capacityState' => 'available',
        'provider' => ['id' => 'loki', 'label' => 'Microsoft 365 Copilot', 'kind' => 'loki'],
        'quotaId' => 'monthly',
        'unit' => 'ai_credits',
        'availableQuantity' => 0,
        'entitledQuantity' => null,
        'hasQuota' => false,
    ]);
    $projection = SessionQuotaGetResult::fromArray([
        'snapshots' => [
            'chat' => [
                'isUnlimitedEntitlement' => false,
                'entitlementRequests' => 100,
                'usedRequests' => 25,
                'usageAllowedWithExhaustedQuota' => false,
                'overage' => 0,
                'overageAllowedWithExhaustedQuota' => false,
                'remainingPercentage' => 75,
            ],
        ],
        'providerQuotas' => [$quota->toArray()],
        'isFreeUser' => false,
        'isTbbUser' => false,
        'planTier' => 'pro',
        'premiumRequestsBillable' => true,
        'modelCostColumnVisible' => true,
        'delegateAvailable' => true,
        'canSignupForCopilotFree' => false,
        'dynamicWorkflowsEnabled' => false,
        'dynamicWorkflowsUiVisible' => false,
    ]);

    expect($quota->accessState)->toBe(ProviderQuotaAccessState::ALLOWED)
        ->and($quota->capacityState)->toBe(ProviderQuotaCapacityState::AVAILABLE)
        ->and($quota->provider)->toBeInstanceOf(ModelProviderRef::class)
        ->and($quota->provider->kind)->toBe(ModelProviderKind::LOKI)
        ->and($quota->toArray())->toHaveKey('availableQuantity', 0)
        ->and($quota->toArray())->toHaveKey('entitledQuantity', null)
        ->and($quota->toArray())->toHaveKey('hasQuota', false)
        ->and($projection->snapshots['chat']->remainingPercentage)->toBe(75)
        ->and($projection->planTier)->toBe(SessionQuotaPlanTier::PRO)
        ->and($projection->providerQuotas[0]->unit)->toBe(ProviderQuotaUnit::AI_CREDITS);

    $eventData = ProviderQuotaObservationData::fromArray(['observation' => $quota->toArray()]);

    expect($eventData->observation->quotaId)->toBe('monthly');
});

it('round trips provider quota budget and monthly usage details', function () {
    $budget = ProviderQuotaBudgetMetadata::fromArray([
        'consumed' => 12.5,
        'entitlement' => 100,
        'overage' => 0,
        'overageAllowedWhenExhausted' => false,
        'remainingPercentage' => 87.5,
        'unlimited' => false,
        'usageAllowedWhenExhausted' => false,
    ]);
    $monthlyUsage = ProviderMonthlyUsage::fromArray([
        'consumedQuantity' => 0,
        'scope' => 'user',
        'state' => 'available',
        'unit' => 'ai_credits',
    ]);

    expect($budget->toArray()['consumed'])->toBe(12.5)
        ->and($monthlyUsage->scope)->toBe(ProviderMonthlyUsageScope::USER)
        ->and($monthlyUsage->state)->toBe(ProviderMonthlyUsageState::AVAILABLE)
        ->and($monthlyUsage->toArray())->toHaveKey('consumedQuantity', 0);
});

it('decodes the access plan tier on session quota projections', function () {
    $projection = SessionQuotaGetResult::fromArray(['planTier' => 'access']);

    expect($projection->planTier)->toBe(SessionQuotaPlanTier::ACCESS)
        ->and($projection->toArray())->toHaveKey('planTier', 'access');
});
