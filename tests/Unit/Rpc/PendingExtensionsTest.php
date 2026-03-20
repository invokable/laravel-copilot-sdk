<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingExtensions;
use Revolution\Copilot\Types\Rpc\SessionExtensionsDisableParams;
use Revolution\Copilot\Types\Rpc\SessionExtensionsEnableParams;
use Revolution\Copilot\Types\Rpc\SessionExtensionsListResult;

describe('PendingExtensions', function () {
    it('calls session.extensions.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.extensions.list', ['sessionId' => 'session-abc'])
            ->andReturn([
                'extensions' => [
                    [
                        'id' => 'project:my-ext',
                        'name' => 'my-ext',
                        'source' => 'project',
                        'status' => 'running',
                        'pid' => 12345,
                    ],
                ],
            ]);

        $pending = new PendingExtensions($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(SessionExtensionsListResult::class)
            ->and($result->extensions)->toHaveCount(1)
            ->and($result->extensions[0]->id)->toBe('project:my-ext');
    });

    it('calls session.extensions.list with empty result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.extensions.list', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingExtensions($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(SessionExtensionsListResult::class)
            ->and($result->extensions)->toBe([]);
    });

    it('calls session.extensions.enable with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.extensions.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['id'] === 'project:my-ext'),
            )
            ->andReturn([]);

        $pending = new PendingExtensions($client, 'session-abc');
        $result = $pending->enable(new SessionExtensionsEnableParams(id: 'project:my-ext'));

        expect($result)->toBe([]);
    });

    it('calls session.extensions.enable with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.extensions.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['id'] === 'user:auth-helper'),
            )
            ->andReturn([]);

        $pending = new PendingExtensions($client, 'session-abc');
        $result = $pending->enable(['id' => 'user:auth-helper']);

        expect($result)->toBe([]);
    });

    it('calls session.extensions.disable with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.extensions.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['id'] === 'project:my-ext'),
            )
            ->andReturn([]);

        $pending = new PendingExtensions($client, 'session-abc');
        $result = $pending->disable(new SessionExtensionsDisableParams(id: 'project:my-ext'));

        expect($result)->toBe([]);
    });

    it('calls session.extensions.disable with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.extensions.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['id'] === 'user:auth-helper'),
            )
            ->andReturn([]);

        $pending = new PendingExtensions($client, 'session-abc');
        $result = $pending->disable(['id' => 'user:auth-helper']);

        expect($result)->toBe([]);
    });

    it('calls session.extensions.reload', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.extensions.reload', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingExtensions($client, 'session-abc');
        $result = $pending->reload();

        expect($result)->toBe([]);
    });
});
