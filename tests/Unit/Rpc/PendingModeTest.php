<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMode;
use Revolution\Copilot\Types\Rpc\SessionModeGetResult;
use Revolution\Copilot\Types\Rpc\SessionModeSetParams;
use Revolution\Copilot\Types\Rpc\SessionModeSetResult;

describe('PendingMode', function () {
    it('calls session.mode.get and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mode.get', ['sessionId' => 'session-abc'])
            ->andReturn(['mode' => 'interactive']);

        $pending = new PendingMode($client, 'session-abc');
        $result = $pending->get();

        expect($result)->toBeInstanceOf(SessionModeGetResult::class)
            ->and($result->mode)->toBe('interactive');
    });

    it('calls session.mode.set with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mode.set',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['mode'] === 'autopilot'),
            )
            ->andReturn(['mode' => 'autopilot']);

        $pending = new PendingMode($client, 'session-abc');
        $result = $pending->set(new SessionModeSetParams(mode: 'autopilot'));

        expect($result)->toBeInstanceOf(SessionModeSetResult::class)
            ->and($result->mode)->toBe('autopilot');
    });

    it('calls session.mode.set with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mode.set',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['mode'] === 'plan'),
            )
            ->andReturn(['mode' => 'plan']);

        $pending = new PendingMode($client, 'session-abc');
        $result = $pending->set(['mode' => 'plan']);

        expect($result)->toBeInstanceOf(SessionModeSetResult::class)
            ->and($result->mode)->toBe('plan');
    });
});
