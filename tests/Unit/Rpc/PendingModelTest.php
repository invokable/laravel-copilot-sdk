<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\FusionTier;
use Revolution\Copilot\Enums\ModelSwitchAutoTierStatus;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingModel;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\ModelApplyStartupOverlayRequest;
use Revolution\Copilot\Types\Rpc\ModelClearStartupSeedRequest;
use Revolution\Copilot\Types\Rpc\ModelContextTierOption;
use Revolution\Copilot\Types\Rpc\ModelGetReasoningSummariesRequest;
use Revolution\Copilot\Types\Rpc\ModelGetReasoningSummariesResult;
use Revolution\Copilot\Types\Rpc\ModelSelectionPolicyEntry;
use Revolution\Copilot\Types\Rpc\ModelSetReasoningEffortRequest;
use Revolution\Copilot\Types\Rpc\ModelSetReasoningEffortResult;
use Revolution\Copilot\Types\Rpc\ModelStartupAdmissionRequest;
use Revolution\Copilot\Types\Rpc\ModelStartupAdmissionResult;
use Revolution\Copilot\Types\Rpc\ModelSwitchAutoTierRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchAutoTierResult;
use Revolution\Copilot\Types\Rpc\ModelSwitchFusionTierRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToResult;
use Revolution\Copilot\Types\Rpc\SessionModelGetSelectionPolicyResult;

describe('PendingModel', function () {
    it('gets and round trips cached selection policy with typed model entries', function () {
        $response = [
            'ready' => true,
            'models' => [
                [
                    'modelId' => 'model-a',
                    'providerId' => 'provider-a',
                    'multiplier' => 1.5,
                    'needsEnablement' => true,
                    'pickerCategory' => 'future-picker-category',
                    'priceCategory' => 'future-price-category',
                    'reasoningEfforts' => ['high', 'future-effort'],
                    'defaultReasoningEffort' => 'future-effort',
                    'contextTiers' => [
                        ['value' => 'future-tier', 'name' => '256K'],
                    ],
                    'defaultContextTier' => 'future-tier',
                ],
                [
                    'modelId' => 'registered-model',
                    'multiplier' => 0.0,
                    'needsEnablement' => false,
                    'reasoningEfforts' => [],
                    'contextTiers' => [],
                ],
                [
                    'modelId' => 'model-without-tiers',
                    'multiplier' => 1.0,
                    'needsEnablement' => false,
                    'reasoningEfforts' => [],
                ],
            ],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.getSelectionPolicy', ['sessionId' => 'session-xyz'])
            ->andReturn($response);

        $result = (new SessionRpc($client, 'session-xyz'))->model()->getSelectionPolicy();

        expect($result)->toBeInstanceOf(SessionModelGetSelectionPolicyResult::class)
            ->and($result->ready)->toBeTrue()
            ->and($result->models[0])->toBeInstanceOf(ModelSelectionPolicyEntry::class)
            ->and($result->models[0]->modelId)->toBe('model-a')
            ->and($result->models[0]->providerId)->toBe('provider-a')
            ->and($result->models[0]->multiplier)->toBe(1.5)
            ->and($result->models[0]->needsEnablement)->toBeTrue()
            ->and($result->models[0]->pickerCategory)->toBe('future-picker-category')
            ->and($result->models[0]->priceCategory)->toBe('future-price-category')
            ->and($result->models[0]->reasoningEfforts)->toBe(['high', 'future-effort'])
            ->and($result->models[0]->defaultReasoningEffort)->toBe('future-effort')
            ->and($result->models[0]->contextTiers[0])->toBeInstanceOf(ModelContextTierOption::class)
            ->and($result->models[0]->contextTiers[0]->value)->toBe('future-tier')
            ->and($result->models[0]->contextTiers[0]->name)->toBe('256K')
            ->and($result->models[0]->defaultContextTier)->toBe('future-tier')
            ->and($result->models[1]->contextTiers)->toBe([])
            ->and($result->models[2]->contextTiers)->toBeNull()
            ->and($result->models[2]->providerId)->toBeNull()
            ->and($result->toArray())->toBe($response);
    });

    it('returns an empty selection policy when the catalog is not ready', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.getSelectionPolicy', ['sessionId' => 'session-xyz'])
            ->andReturn(['ready' => false, 'models' => []]);

        $result = (new SessionRpc($client, 'session-xyz'))->model()->getSelectionPolicy();

        expect($result)->toBeInstanceOf(SessionModelGetSelectionPolicyResult::class)
            ->and($result->ready)->toBeFalse()
            ->and($result->models)->toBe([])
            ->and($result->toArray())->toBe(['ready' => false, 'models' => []]);
    });

    it('claims startup admission with typed parameters and returns the cached model state', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.startupAdmission', ['claimEnablement' => true, 'sessionId' => 'session-xyz'])
            ->andReturn([
                'selectedModel' => 'model-a',
                'catalogReady' => true,
                'requiresEnablement' => true,
                'claimedEnablement' => true,
                'modelToEnable' => 'model-a',
            ]);

        $result = (new SessionRpc($client, 'session-xyz'))->model()->startupAdmission(
            new ModelStartupAdmissionRequest(claimEnablement: true),
        );

        expect($result)->toBeInstanceOf(ModelStartupAdmissionResult::class)
            ->and($result->selectedModel)->toBe('model-a')
            ->and($result->catalogReady)->toBeTrue()
            ->and($result->requiresEnablement)->toBeTrue()
            ->and($result->claimedEnablement)->toBeTrue()
            ->and($result->modelToEnable)->toBe('model-a');
    });

    it('sends an optional startup admission claim without overriding the session', function ($params, $expected) {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.startupAdmission', $expected)
            ->andReturn(['catalogReady' => false, 'requiresEnablement' => false, 'claimedEnablement' => false]);

        $model = (new SessionRpc($client, 'session-xyz'))->model();
        $result = $params === null ? $model->startupAdmission() : $model->startupAdmission($params);

        expect($result->selectedModel)->toBeNull()
            ->and($result->catalogReady)->toBeFalse()
            ->and($result->requiresEnablement)->toBeFalse()
            ->and($result->claimedEnablement)->toBeFalse()
            ->and($result->modelToEnable)->toBeNull();
    })->with([
        [null, ['sessionId' => 'session-xyz']],
        [['claimEnablement' => false, 'sessionId' => 'other'], ['claimEnablement' => false, 'sessionId' => 'session-xyz']],
        [['claimEnablement' => true], ['claimEnablement' => true, 'sessionId' => 'session-xyz']],
    ]);

    it('calls session.model.getCurrent and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.getCurrent', ['sessionId' => 'session-xyz'])
            ->andReturn(['modelId' => 'gpt-4o']);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->getCurrent();

        expect($result)->toBeInstanceOf(CurrentModel::class)
            ->and($result->modelId)->toBe('gpt-4o');
    });

    it('calls session.model.getCurrent with null modelId', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.getCurrent', ['sessionId' => 'session-xyz'])
            ->andReturn([]);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->getCurrent();

        expect($result)->toBeInstanceOf(CurrentModel::class)
            ->and($result->modelId)->toBeNull();
    });

    it('gets reasoning-summary policy with the exact session request payload', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.getReasoningSummaries', [
                'persistedShowReasoning' => false,
                'summariesVisible' => true,
                'sessionId' => 'session-xyz',
            ])
            ->andReturn([
                'ready' => true,
                'experimentSettled' => true,
                'sessionShowsSummaries' => true,
                'modelDefaultShowsSummaries' => false,
                'showReasoningHeaders' => true,
                'visibility' => false,
                'showHiddenHint' => false,
                'clearRedundantSetting' => true,
            ]);

        $result = (new PendingModel($client, 'session-xyz'))->getReasoningSummaries(
            new ModelGetReasoningSummariesRequest(
                persistedShowReasoning: false,
                summariesVisible: true,
            ),
        );

        expect($result)->toBeInstanceOf(ModelGetReasoningSummariesResult::class)
            ->and($result->ready)->toBeTrue()
            ->and($result->visibility)->toBeFalse()
            ->and($result->clearRedundantSetting)->toBeTrue();
    });

    it('omits unset reasoning-summary options from the session request payload', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.getReasoningSummaries', ['sessionId' => 'session-xyz'])
            ->andReturn([]);

        $result = (new PendingModel($client, 'session-xyz'))->getReasoningSummaries();

        expect($result->ready)->toBeFalse()
            ->and($result->visibility)->toBeNull();
    });

    it('updates reasoning effort without switching models', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.setReasoningEffort', [
                'reasoningEffort' => 'high',
                'sessionId' => 'session-xyz',
            ])
            ->andReturn(['reasoningEffort' => 'high']);

        $result = (new PendingModel($client, 'session-xyz'))->setReasoningEffort(
            new ModelSetReasoningEffortRequest(reasoningEffort: ReasoningEffort::HIGH),
        );

        expect($result)->toBeInstanceOf(ModelSetReasoningEffortResult::class)
            ->and($result->reasoningEffort)->toBe(ReasoningEffort::HIGH);
    });

    it('accepts array parameters when updating reasoning effort', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.setReasoningEffort', [
                'reasoningEffort' => 'low',
                'sessionId' => 'session-xyz',
            ])
            ->andReturn(['reasoningEffort' => 'low']);

        $result = (new PendingModel($client, 'session-xyz'))->setReasoningEffort([
            'reasoningEffort' => 'low',
        ]);

        expect($result->reasoningEffort)->toBe(ReasoningEffort::LOW);
    });

    it('calls session.model.switchTo with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.model.switchTo',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['modelId'] === 'claude-opus-4'),
            )
            ->andReturn(['modelId' => 'claude-opus-4']);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->switchTo(new ModelSwitchToRequest(modelId: 'claude-opus-4'));

        expect($result)->toBeInstanceOf(ModelSwitchToResult::class)
            ->and($result->modelId)->toBe('claude-opus-4');
    });

    it('calls session.model.switchTo with reasoningEffort', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.model.switchTo',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['modelId'] === 'o1-preview'
                    && $params['reasoningEffort'] === 'high'),
            )
            ->andReturn(['modelId' => 'o1-preview']);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->switchTo(new ModelSwitchToRequest(
            modelId: 'o1-preview',
            reasoningEffort: ReasoningEffort::HIGH,
        ));

        expect($result)->toBeInstanceOf(ModelSwitchToResult::class)
            ->and($result->modelId)->toBe('o1-preview');
    });

    it('calls session.model.switchTo with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.model.switchTo',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['modelId'] === 'gpt-5'),
            )
            ->andReturn(['modelId' => 'gpt-5']);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->switchTo(['modelId' => 'gpt-5']);

        expect($result)->toBeInstanceOf(ModelSwitchToResult::class)
            ->and($result->modelId)->toBe('gpt-5');
    });

    it('calls session.model.switchAutoTier with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.model.switchAutoTier',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['autoTier'] === 'balance'),
            )
            ->andReturn(['status' => 'pending', 'effectiveAutoTier' => 'balance']);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->switchAutoTier(new ModelSwitchAutoTierRequest(autoTier: AutoTier::BALANCE));

        expect($result)->toBeInstanceOf(ModelSwitchAutoTierResult::class)
            ->and($result->status)->toBe(ModelSwitchAutoTierStatus::PENDING)
            ->and($result->effectiveAutoTier)->toBe(AutoTier::BALANCE);
    });

    it('calls session.model.switchAutoTier with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.model.switchAutoTier',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['autoTier'] === null),
            )
            ->andReturn(['status' => 'unchanged']);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->switchAutoTier([]);

        expect($result)->toBeInstanceOf(ModelSwitchAutoTierResult::class)
            ->and($result->status)->toBe(ModelSwitchAutoTierStatus::UNCHANGED);
    });

    it('calls session.model.switchFusionTier with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.model.switchFusionTier',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['fusionTier'] === 'max'),
            )
            ->andReturn(['status' => 'pending', 'effectiveAutoTier' => 'intelligence', 'pendingFusionTier' => 'max']);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->switchFusionTier(new ModelSwitchFusionTierRequest(fusionTier: FusionTier::MAX));

        expect($result)->toBeInstanceOf(ModelSwitchAutoTierResult::class)
            ->and($result->status)->toBe(ModelSwitchAutoTierStatus::PENDING)
            ->and($result->effectiveAutoTier)->toBe(AutoTier::INTELLIGENCE)
            ->and($result->pendingFusionTier)->toBe('max');
    });

    it('calls session.model.switchFusionTier with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.model.switchFusionTier',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['fusionTier'] === 'high'),
            )
            ->andReturn(['status' => 'unchanged']);

        $pending = new PendingModel($client, 'session-xyz');
        $result = $pending->switchFusionTier(['fusionTier' => 'high']);

        expect($result->status)->toBe(ModelSwitchAutoTierStatus::UNCHANGED);
    });

    it('clears a matching startup model seed through the internal typed RPC', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.clearStartupSeed', [
                'expectedModel' => 'gpt-5',
                'expectedProviderId' => 'loki',
                'sessionId' => 'session-1',
            ])
            ->andReturn(['cleared' => true]);

        $result = (new SessionRpc($client, 'session-1'))->model()->clearStartupSeed(
            new ModelClearStartupSeedRequest('gpt-5', 'loki'),
        );

        expect($result->cleared)->toBeTrue();
    });

    it('applies repository model overlays with provider identity', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.model.applyStartupOverlay', [
                'repoModel' => 'gpt-5',
                'repoModelProviderId' => 'loki',
                'sessionId' => 'session-1',
            ])
            ->andReturn(['modelId' => 'gpt-5']);

        $result = (new SessionRpc($client, 'session-1'))->model()->applyStartupOverlay(
            new ModelApplyStartupOverlayRequest(
                repoModel: 'gpt-5',
                repoModelProviderId: 'loki',
            ),
        );

        expect($result->modelId)->toBe('gpt-5');
    });
});
