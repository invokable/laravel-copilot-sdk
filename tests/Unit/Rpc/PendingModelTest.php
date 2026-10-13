<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\ModelSwitchAutoTierStatus;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingModel;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\ModelApplyStartupOverlayRequest;
use Revolution\Copilot\Types\Rpc\ModelClearStartupSeedRequest;
use Revolution\Copilot\Types\Rpc\ModelGetReasoningSummariesRequest;
use Revolution\Copilot\Types\Rpc\ModelGetReasoningSummariesResult;
use Revolution\Copilot\Types\Rpc\ModelSwitchAutoTierRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchAutoTierResult;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToResult;

describe('PendingModel', function () {
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
