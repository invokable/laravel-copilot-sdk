<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingName;
use Revolution\Copilot\Types\Rpc\NameGetResult;
use Revolution\Copilot\Types\Rpc\NameSetRequest;

describe('PendingName', function () {
    it('calls session.name.get and returns NameGetResult', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.name.get', ['sessionId' => 'session-abc'])
            ->andReturn(['name' => 'My Session']);

        $pending = new PendingName($client, 'session-abc');
        $result = $pending->get();

        expect($result)->toBeInstanceOf(NameGetResult::class)
            ->and($result->name)->toBe('My Session');
    });

    it('returns null name when not set', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->andReturn(['name' => null]);

        $pending = new PendingName($client, 'session-abc');
        $result = $pending->get();

        expect($result)->toBeInstanceOf(NameGetResult::class)
            ->and($result->name)->toBeNull();
    });

    it('calls session.name.set with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.name.set',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['name'] === 'New Name'),
            )
            ->andReturn(null);

        $pending = new PendingName($client, 'session-abc');
        $pending->set(new NameSetRequest(name: 'New Name'));
    });

    it('calls session.name.set with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.name.set',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['name'] === 'Array Name'),
            )
            ->andReturn(null);

        $pending = new PendingName($client, 'session-abc');
        $pending->set(['name' => 'Array Name']);
    });
});
