<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMode;
use Revolution\Copilot\Types\Rpc\ModeSetRequest;
use Revolution\Copilot\Types\Rpc\ModeSetResult;

describe('PendingMode', function () {
    it('calls session.mode.get and returns mode string', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mode.get', ['sessionId' => 'session-abc'])
            ->andReturn('interactive');

        $pending = new PendingMode($client, 'session-abc');
        $result = $pending->get();

        expect($result)->toBe('interactive');
    });

    it('calls session.mode.set with typed params and returns mode-change details', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mode.set',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['mode'] === 'autopilot'),
            )
            ->andReturn(['status' => 'applied', 'modelChanged' => true, 'modeApplied' => true]);

        $pending = new PendingMode($client, 'session-abc');
        $result = $pending->set(new ModeSetRequest(mode: 'autopilot'));

        expect($result)->toBeInstanceOf(ModeSetResult::class)
            ->and($result->modeApplied)->toBeTrue()
            ->and($result->modelChanged)->toBeTrue();
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
            ->andReturn(['status' => 'precondition_failed', 'modelChanged' => false, 'modeApplied' => false]);

        $pending = new PendingMode($client, 'session-abc');
        expect($pending->set(['mode' => 'plan'])->modeApplied)->toBeFalse();
    });
});
