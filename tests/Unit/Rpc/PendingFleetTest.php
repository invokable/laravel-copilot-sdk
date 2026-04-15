<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingFleet;
use Revolution\Copilot\Types\Rpc\FleetStartRequest;
use Revolution\Copilot\Types\Rpc\FleetStartResult;

describe('PendingFleet', function () {
    it('calls session.fleet.start with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.fleet.start',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-fleet'
                    && $params['prompt'] === 'Run all tests'),
            )
            ->andReturn(['started' => true]);

        $pending = new PendingFleet($client, 'session-fleet');
        $result = $pending->start(new FleetStartRequest(prompt: 'Run all tests'));

        expect($result)->toBeInstanceOf(FleetStartResult::class)
            ->and($result->started)->toBeTrue();
    });

    it('calls session.fleet.start with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.fleet.start',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-fleet'
                    && $params['prompt'] === 'Deploy'),
            )
            ->andReturn(['started' => true]);

        $pending = new PendingFleet($client, 'session-fleet');
        $result = $pending->start(['prompt' => 'Deploy']);

        expect($result)->toBeInstanceOf(FleetStartResult::class)
            ->and($result->started)->toBeTrue();
    });

    it('calls session.fleet.start with no prompt', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.fleet.start',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-fleet'
                    && ! isset($params['prompt'])),
            )
            ->andReturn(['started' => false]);

        $pending = new PendingFleet($client, 'session-fleet');
        $result = $pending->start();

        expect($result)->toBeInstanceOf(FleetStartResult::class)
            ->and($result->started)->toBeFalse();
    });
});
