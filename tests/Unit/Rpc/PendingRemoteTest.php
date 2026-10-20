<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingRemote;
use Revolution\Copilot\Types\Rpc\RemoteEnableResult;
use Revolution\Copilot\Types\Rpc\RemoteNotifySteerableChangedRequest;
use Revolution\Copilot\Types\Rpc\RemoteSessionConnectionResult;

describe('PendingRemote', function () {
    it('calls session.remote.enable and returns RemoteEnableResult', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.remote.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-remote-1'),
            )
            ->andReturn(['remoteSteerable' => true, 'url' => 'https://github.com/mission-control/session/123']);

        $pending = new PendingRemote($client, 'session-remote-1');
        $result = $pending->enable();

        expect($result)->toBeInstanceOf(RemoteEnableResult::class)
            ->and($result->remoteSteerable)->toBeTrue()
            ->and($result->url)->toBe('https://github.com/mission-control/session/123');
    });

    it('enable returns RemoteEnableResult with no url when not provided', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.remote.enable', Mockery::type('array'))
            ->andReturn(['remoteSteerable' => false]);

        $pending = new PendingRemote($client, 'session-remote-2');
        $result = $pending->enable();

        expect($result)->toBeInstanceOf(RemoteEnableResult::class)
            ->and($result->remoteSteerable)->toBeFalse()
            ->and($result->url)->toBeNull();
    });

    it('calls session.remote.disable and returns void', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.remote.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-remote-3'),
            )
            ->andReturn([]);

        $pending = new PendingRemote($client, 'session-remote-3');
        $result = $pending->disable();

        expect($result)->toBeNull();
    });

    it('connects a remote session using its session-scoped RPC', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.remote.connectRemoteSession',
                ['sessionId' => 'bound-session'],
            )
            ->andReturn([
                'metadata' => [
                    'kind' => 'coding-agent',
                    'modifiedTime' => '2024-01-01T00:00:00Z',
                    'repository' => ['branch' => 'main', 'name' => 'repo', 'owner' => 'org'],
                    'sessionId' => 'remote-session',
                    'startTime' => '2024-01-01T00:00:00Z',
                ],
                'sessionId' => 'connected-session',
            ]);

        $result = (new PendingRemote($client, 'bound-session'))->connectRemoteSession([
            'sessionId' => 'remote-session',
        ]);

        expect($result)->toBeInstanceOf(RemoteSessionConnectionResult::class)
            ->and($result->sessionId)->toBe('connected-session');
    });

    it('reports remote-steerability changes for its session', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.remote.notifySteerableChanged', [
                'remoteSteerable' => false,
                'sessionId' => 'session-remote-4',
            ])
            ->andReturn([]);

        $pending = new PendingRemote($client, 'session-remote-4');
        $result = $pending->notifySteerableChanged(
            new RemoteNotifySteerableChangedRequest(remoteSteerable: false),
        );

        expect($result)->toBeNull();
    });
});
