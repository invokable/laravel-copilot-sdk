<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AiCreditsStatus;
use Revolution\Copilot\Enums\ModelProviderKind;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\Rpc\AccountStatus;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\MetadataContextInfoRequest;
use Revolution\Copilot\Types\Rpc\ModelApplyStartupOverlayRequest;
use Revolution\Copilot\Types\Rpc\ModelProviderRef;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\ModeSetRequest;
use Revolution\Copilot\Types\Rpc\SessionContextInfo;
use Revolution\Copilot\Types\Rpc\UsageGetMetricsResult;
use Revolution\Copilot\Types\Rpc\UsageMetricsAgentMetric;
use Revolution\Copilot\Types\Rpc\UsageMetricsModelMetricTokenDetail;
use Revolution\Copilot\Types\Rpc\UsageMetricsProviderModelMetric;
use Revolution\Copilot\Types\Rpc\UsageMetricsTokenDetail;

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
