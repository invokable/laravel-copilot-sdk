<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMode;
use Revolution\Copilot\Types\Rpc\ModeSetRequest;

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

    it('calls session.mode.set with typed params and returns void', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mode.set',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['mode'] === 'autopilot'),
            )
            ->andReturn(null);

        $pending = new PendingMode($client, 'session-abc');
        $pending->set(new ModeSetRequest(mode: 'autopilot'));
    });

    it('calls session.mode.set with array params and returns void', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mode.set',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['mode'] === 'plan'),
            )
            ->andReturn(null);

        $pending = new PendingMode($client, 'session-abc');
        $pending->set(['mode' => 'plan']);
    });
});
