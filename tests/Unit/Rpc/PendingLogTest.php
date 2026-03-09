<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\LogLevel;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingLog;
use Revolution\Copilot\Types\Rpc\SessionLogParams;
use Revolution\Copilot\Types\Rpc\SessionLogResult;

describe('PendingLog', function () {
    it('calls session.log with correct params using SessionLogParams', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.log',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['message'] === 'Processing started'
                    && ! isset($params['level'])
                    && ! isset($params['ephemeral'])),
            )
            ->andReturn(['eventId' => 'evt-123']);

        $pending = new PendingLog($client, 'test-session-id');
        $result = $pending->log(new SessionLogParams(message: 'Processing started'));

        expect($result)->toBeInstanceOf(SessionLogResult::class)
            ->and($result->eventId)->toBe('evt-123');
    });

    it('calls session.log with level and ephemeral params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.log',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['message'] === 'Disk usage high'
                    && $params['level'] === 'warning'
                    && $params['ephemeral'] === true),
            )
            ->andReturn(['eventId' => 'evt-456']);

        $pending = new PendingLog($client, 'test-session-id');
        $result = $pending->log(new SessionLogParams(
            message: 'Disk usage high',
            level: LogLevel::WARNING,
            ephemeral: true,
        ));

        expect($result)->toBeInstanceOf(SessionLogResult::class)
            ->and($result->eventId)->toBe('evt-456');
    });

    it('calls session.log with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.log',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['message'] === 'Error occurred'
                    && $params['level'] === 'error'),
            )
            ->andReturn(['eventId' => 'evt-789']);

        $pending = new PendingLog($client, 'test-session-id');
        $result = $pending->log([
            'message' => 'Error occurred',
            'level' => 'error',
        ]);

        expect($result)->toBeInstanceOf(SessionLogResult::class)
            ->and($result->eventId)->toBe('evt-789');
    });

    it('overrides sessionId when provided as array param', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.log',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'),
            )
            ->andReturn(['eventId' => 'evt-abc']);

        $pending = new PendingLog($client, 'test-session-id');
        $result = $pending->log([
            'message' => 'Test',
            'sessionId' => 'some-other-session',
        ]);

        expect($result)->toBeInstanceOf(SessionLogResult::class)
            ->and($result->eventId)->toBe('evt-abc');
    });
});
