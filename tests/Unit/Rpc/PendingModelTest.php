<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingModel;
use Revolution\Copilot\Types\Rpc\CurrentModel;
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
});
