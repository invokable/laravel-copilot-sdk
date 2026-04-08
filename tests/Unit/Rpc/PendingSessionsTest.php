<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessions;
use Revolution\Copilot\Types\Rpc\SessionsForkParams;
use Revolution\Copilot\Types\Rpc\SessionsForkResult;

describe('PendingSessions', function () {
    it('calls sessions.fork with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'sessions.fork',
                Mockery::on(fn ($params) => $params['sessionId'] === 'source-session'
                    && ! isset($params['toEventId'])),
            )
            ->andReturn([
                'sessionId' => 'forked-session-id',
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->fork(new SessionsForkParams(sessionId: 'source-session'));

        expect($result)->toBeInstanceOf(SessionsForkResult::class)
            ->and($result->sessionId)->toBe('forked-session-id');
    });

    it('calls sessions.fork with toEventId', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'sessions.fork',
                Mockery::on(fn ($params) => $params['sessionId'] === 'source-session'
                    && $params['toEventId'] === 'evt-boundary'),
            )
            ->andReturn([
                'sessionId' => 'forked-session-id',
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->fork(new SessionsForkParams(
            sessionId: 'source-session',
            toEventId: 'evt-boundary',
        ));

        expect($result)->toBeInstanceOf(SessionsForkResult::class)
            ->and($result->sessionId)->toBe('forked-session-id');
    });

    it('calls sessions.fork with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'sessions.fork',
                Mockery::on(fn ($params) => $params['sessionId'] === 'source-session'),
            )
            ->andReturn([
                'sessionId' => 'forked-session-id',
            ]);

        $pending = new PendingSessions($client);
        $result = $pending->fork(['sessionId' => 'source-session']);

        expect($result)->toBeInstanceOf(SessionsForkResult::class)
            ->and($result->sessionId)->toBe('forked-session-id');
    });
});
