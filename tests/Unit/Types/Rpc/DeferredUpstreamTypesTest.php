<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AiCreditsStatus;
use Revolution\Copilot\Enums\ManagedPermissionVerdict;
use Revolution\Copilot\Enums\ManagedPluginProgressPhase;
use Revolution\Copilot\Enums\ModelCallRequestBodyEncoding;
use Revolution\Copilot\Enums\ModelCallWebSocketFallbackErrorKind;
use Revolution\Copilot\Enums\ModelProviderKind;
use Revolution\Copilot\Enums\ProviderMonthlyUsageScope;
use Revolution\Copilot\Enums\ProviderMonthlyUsageState;
use Revolution\Copilot\Enums\ProviderQuotaAccessState;
use Revolution\Copilot\Enums\ProviderQuotaCapacityState;
use Revolution\Copilot\Enums\ProviderQuotaUnit;
use Revolution\Copilot\Enums\SessionQuotaPlanTier;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\Rpc\AccountStatus;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\HostCreateSessionRequest;
use Revolution\Copilot\Types\Rpc\HostGitHubEnvironmentOptions;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostListSessionsResult;
use Revolution\Copilot\Types\Rpc\HostStartRequest;
use Revolution\Copilot\Types\Rpc\ManagedPermissionsContext;
use Revolution\Copilot\Types\Rpc\ManagedPluginProgressData;
use Revolution\Copilot\Types\Rpc\ManagedSettingsPermissionsEvaluateRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsPermissionsEvaluateResult;
use Revolution\Copilot\Types\Rpc\MetadataContextInfoRequest;
use Revolution\Copilot\Types\Rpc\ModelApplyStartupOverlayRequest;
use Revolution\Copilot\Types\Rpc\ModelMetric;
use Revolution\Copilot\Types\Rpc\ModelMetricRequests;
use Revolution\Copilot\Types\Rpc\ModelMetricUsage;
use Revolution\Copilot\Types\Rpc\ModelProviderRef;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\ModeSetRequest;
use Revolution\Copilot\Types\Rpc\ProviderMonthlyUsage;
use Revolution\Copilot\Types\Rpc\ProviderQuotaBudgetMetadata;
use Revolution\Copilot\Types\Rpc\ProviderQuotaObservationData;
use Revolution\Copilot\Types\Rpc\ProviderQuotaState;
use Revolution\Copilot\Types\Rpc\QuotaWarningProjection;
use Revolution\Copilot\Types\Rpc\SessionContextInfo;
use Revolution\Copilot\Types\Rpc\SessionPluginsRetryManagedResult;
use Revolution\Copilot\Types\Rpc\SessionQuotaGetResult;
use Revolution\Copilot\Types\Rpc\UsageGetMetricsResult;
use Revolution\Copilot\Types\Rpc\UsageMetricsAgentMetric;
use Revolution\Copilot\Types\Rpc\UsageMetricsModelMetricTokenDetail;
use Revolution\Copilot\Types\Rpc\UsageMetricsProviderModelMetric;
use Revolution\Copilot\Types\Rpc\UsageMetricsTokenDetail;
use Revolution\Copilot\Types\Rpc\UsageSetCodeChangesRequest;

it('round trips the advertised host session catalog', function () {
    $request = HostListSessionsRequest::fromArray(['hostId' => 'host-1']);
    $result = HostListSessionsResult::fromArray([
        'sessions' => [[
            'resource' => 'ahp://host/session-1',
            'title' => 'Review',
            'createdAt' => '2026-10-09T10:00:00Z',
            'modifiedAt' => '2026-10-09T10:15:00Z',
            'status' => 0x80000000,
            'activity' => 'Working',
        ]],
    ]);

    expect($request->toArray())->toBe(['hostId' => 'host-1'])
        ->and($result->sessions[0]->status)->toBe(0x80000000)
        ->and($result->toArray()['sessions'][0]['activity'])->toBe('Working');
});

it('round trips host configuration, connection binding, and resident preference', function () {
    $start = HostStartRequest::fromArray([
        'hostId' => 'host-1',
        'computeId' => 'compute-1',
        'githubEnvironment' => [
            'name' => 'Example',
            'computeId' => 'compute-1',
            'requireConnectionBinding' => false,
        ],
    ]);
    $handoff = HostCreateSessionRequest::fromArray([
        'handoffId' => 'handoff-1',
        'resume' => true,
        'preferResident' => true,
        'config' => ['sessionId' => 'session-1'],
    ]);

    expect($start->githubEnvironment)->toBeInstanceOf(HostGitHubEnvironmentOptions::class)
        ->and($start->toArray()['computeId'])->toBe('compute-1')
        ->and($start->toArray()['githubEnvironment']['requireConnectionBinding'])->toBeFalse()
        ->and($handoff->toArray()['preferResident'])->toBeTrue()
        ->and($handoff->toArray()['config']['sessionId'])->toBe('session-1');
});

it('round trips managed permissions context and ordered evaluations', function () {
    $request = ManagedSettingsPermissionsEvaluateRequest::fromArray([
        'context' => [
            'failClosed' => true,
            'permissions' => ['url' => ['allow' => ['https://example.com']]],
        ],
        'operations' => [
            ['url' => 'https://example.com', 'kind' => 'url'],
            ['url' => 'https://blocked.example', 'kind' => 'url'],
        ],
    ]);
    $result = ManagedSettingsPermissionsEvaluateResult::fromArray([
        'failClosed' => true,
        'results' => [
            ['operation' => ['url' => 'https://example.com', 'kind' => 'url'], 'verdict' => 'allow'],
            ['operation' => ['url' => 'https://blocked.example', 'kind' => 'url'], 'verdict' => 'deny'],
        ],
    ]);

    expect($request->context)->toBeInstanceOf(ManagedPermissionsContext::class)
        ->and($request->toArray()['operations'])->toHaveCount(2)
        ->and($result->results[0]->verdict)->toBe(ManagedPermissionVerdict::ALLOW)
        ->and($result->results[1]->verdict)->toBe(ManagedPermissionVerdict::DENY)
        ->and($result->toArray()['failClosed'])->toBeTrue();
});

it('round trips managed plugin retry results and progress phases', function () {
    $result = SessionPluginsRetryManagedResult::fromArray([
        'plugins' => [
            ['spec' => 'org/plugin@marketplace', 'status' => 'installed'],
            ['spec' => 'org/other@marketplace', 'status' => 'failed', 'error' => 'Unavailable'],
        ],
    ]);
    $progress = ManagedPluginProgressData::fromArray([
        'phase' => 'installing',
        'pluginSpecs' => ['org/plugin@marketplace'],
    ]);

    expect($result->plugins[0]->status->value)->toBe('installed')
        ->and($result->plugins[1]->error)->toBe('Unavailable')
        ->and($progress->phase)->toBe(ManagedPluginProgressPhase::INSTALLING)
        ->and($progress->toArray()['pluginSpecs'])->toBe(['org/plugin@marketplace']);
});

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

it('round trips model provider selection, auth source, and AI-credit status', function () {
    $current = CurrentModel::fromArray([
        'modelId' => 'gpt-5',
        'providerId' => 'loki',
        'planBaseProviderId' => 'copilot',
    ]);
    $switch = new ModelSwitchToRequest(modelId: 'gpt-5', providerId: 'loki');
    $account = AccountStatus::fromArray([
        'host' => 'github.com',
        'login' => 'octocat',
        'kind' => 'githubDotCom',
        'active' => true,
        'selectionId' => 'account-1',
        'authSource' => 'gh',
    ]);
    $metrics = UsageGetMetricsResult::fromArray([
        'totalPremiumRequestCost' => 0,
        'totalUserRequests' => 0,
        'totalApiDurationMs' => 0,
        'sessionStartTime' => '2026-10-09T10:00:00Z',
        'codeChanges' => ['linesAdded' => 0, 'linesRemoved' => 0, 'filesModifiedCount' => 1, 'filesModified' => ['src/Example.php']],
        'modelMetrics' => [
            'gpt-5' => [
                'requests' => ['count' => 0, 'cost' => 0],
                'usage' => ['inputTokens' => 0, 'outputTokens' => 0, 'cacheReadTokens' => 0, 'cacheWriteTokens' => 0],
                'aiCreditsStatus' => 'partial',
                'cacheExpiresAt' => '2026-10-09T10:05:00Z',
                'tokenDetails' => ['input' => ['tokenCount' => 10]],
                'totalNanoAiu' => 20,
            ],
        ],
        'agentMetrics' => [
            'main' => [
                'agentDisplayName' => 'Main conversation',
                'modelMetrics' => [
                    'gpt-5' => [
                        'requests' => ['count' => 1, 'cost' => 0],
                        'usage' => ['inputTokens' => 10, 'outputTokens' => 2, 'cacheReadTokens' => 0, 'cacheWriteTokens' => 0],
                    ],
                ],
                'totalApiDurationMs' => 12.5,
                'totalNanoAiu' => 30,
            ],
        ],
        'tokenDetails' => ['output' => ['tokenCount' => 2]],
        'totalNanoAiu' => 30,
        'lastCallInputTokens' => 0,
        'lastCallOutputTokens' => 0,
        'aiCreditsStatus' => 'unavailable',
        'providerModelMetrics' => [[
            'metrics' => [
                'requests' => ['count' => 1, 'cost' => 1],
                'usage' => ['inputTokens' => 10, 'outputTokens' => 5, 'cacheReadTokens' => 0, 'cacheWriteTokens' => 0],
            ],
            'modelId' => 'gpt-5',
            'provider' => ['id' => 'loki', 'label' => 'Microsoft 365 Copilot', 'kind' => 'loki'],
            'modelDisplayName' => 'GPT-5',
        ]],
    ]);
    $contextRequest = MetadataContextInfoRequest::fromArray([
        'promptTokenLimit' => 100,
        'outputTokenLimit' => 10,
        'selectedModel' => 'gpt-5',
        'providerId' => 'loki',
    ]);
    $contextInfo = SessionContextInfo::fromArray([
        'modelName' => 'gpt-5',
        'provider' => ['id' => 'loki', 'label' => 'Microsoft 365 Copilot', 'kind' => 'loki'],
        'displayModelName' => 'GPT-5',
    ]);
    $modeRequest = ModeSetRequest::fromArray([
        'mode' => 'plan',
        'planModelProviderId' => 'loki',
    ]);

    expect($current->toArray())->toMatchArray([
        'providerId' => 'loki',
        'planBaseProviderId' => 'copilot',
    ])
        ->and($switch->toArray()['providerId'])->toBe('loki')
        ->and($account->toArray()['authSource'])->toBe('gh')
        ->and($metrics->aiCreditsStatus)->toBe(AiCreditsStatus::UNAVAILABLE)
        ->and($metrics->modelMetrics['gpt-5']->aiCreditsStatus)->toBe(AiCreditsStatus::PARTIAL)
        ->and($metrics->modelMetrics['gpt-5']->tokenDetails['input'])->toBeInstanceOf(UsageMetricsModelMetricTokenDetail::class)
        ->and($metrics->modelMetrics['gpt-5']->toArray()['totalNanoAiu'])->toBe(20)
        ->and($metrics->sessionStartTime)->toBe('2026-10-09T10:00:00Z')
        ->and($metrics->codeChanges->filesModified)->toBe(['src/Example.php'])
        ->and($metrics->agentMetrics['main'])->toBeInstanceOf(UsageMetricsAgentMetric::class)
        ->and($metrics->tokenDetails['output'])->toBeInstanceOf(UsageMetricsTokenDetail::class)
        ->and($metrics->toArray()['totalNanoAiu'])->toBe(30)
        ->and($metrics->providerModelMetrics[0])->toBeInstanceOf(UsageMetricsProviderModelMetric::class)
        ->and($metrics->providerModelMetrics[0]->provider->kind)->toBe(ModelProviderKind::LOKI)
        ->and($contextRequest->toArray()['providerId'])->toBe('loki')
        ->and($contextInfo->toArray()['displayModelName'])->toBe('GPT-5')
        ->and($modeRequest->toArray()['planModelProviderId'])->toBe('loki');
});

it('round trips repository model provider overlays and provider-attributed model info', function () {
    $overlay = ModelApplyStartupOverlayRequest::fromArray([
        'repoModel' => 'gpt-5',
        'repoModelProviderId' => 'loki',
    ]);
    $model = ModelInfo::fromArray([
        'id' => 'gpt-5',
        'name' => 'GPT-5',
        'capabilities' => ['supports' => [], 'limits' => []],
        'provider' => ['id' => 'loki', 'label' => 'Microsoft 365 Copilot', 'kind' => 'loki'],
    ]);

    expect($overlay->toArray()['repoModelProviderId'])->toBe('loki')
        ->and($model->provider)->toBeInstanceOf(ModelProviderRef::class)
        ->and($model->toArray()['provider']['id'])->toBe('loki');
});

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
