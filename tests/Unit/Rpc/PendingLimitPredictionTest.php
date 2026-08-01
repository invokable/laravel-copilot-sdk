<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionLimitPredictionClientType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingLimitPrediction;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionPredictRequest;
use Revolution\Copilot\Types\Rpc\SessionLimitPredictionResult;

describe('PendingLimitPrediction', function () {
    it('calls session.limitPrediction.predict with no params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.limitPrediction.predict', ['sessionId' => 'session-xyz'])
            ->andReturn(['kind' => 'unavailable', 'reason' => 'no_model']);

        $pending = new PendingLimitPrediction($client, 'session-xyz');
        $result = $pending->predict();

        expect($result)->toBeInstanceOf(SessionLimitPredictionResult::class)
            ->and($result->kind->value)->toBe('unavailable');
    });

    it('calls session.limitPrediction.predict with a request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.limitPrediction.predict',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['modelId'] === 'gpt-5'
                    && $params['clientType'] === 'cli-interactive'),
            )
            ->andReturn(['kind' => 'available', 'prediction' => [
                'clientType' => 'cli-interactive',
                'modelId' => 'gpt-5',
                'source' => 'model',
                'sourceKey' => 'gpt-5',
                'tiers' => [['tier' => 'recommended', 'cap' => 100.25]],
                'baselineData' => ['windowStart' => 'a', 'windowEnd' => 'b'],
                'recommendedTier' => 'recommended',
                'recommendedCap' => 100.5,
            ]]);

        $pending = new PendingLimitPrediction($client, 'session-xyz');
        $result = $pending->predict(new SessionLimitPredictionPredictRequest(
            modelId: 'gpt-5',
            clientType: SessionLimitPredictionClientType::CLI_INTERACTIVE,
        ));

        expect($result->kind->value)->toBe('available')
            ->and($result->prediction->recommendedCap)->toBe(100.5);
    });

    it('accepts an array of params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.limitPrediction.predict',
                ['modelId' => 'gpt-5', 'sessionId' => 'session-xyz'],
            )
            ->andReturn(['kind' => 'unavailable', 'reason' => 'auto_unresolved']);

        $pending = new PendingLimitPrediction($client, 'session-xyz');
        $result = $pending->predict(['modelId' => 'gpt-5']);

        expect($result->reason->value)->toBe('auto_unresolved');
    });
});
