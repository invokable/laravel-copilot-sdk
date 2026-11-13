<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingManagedSettings;
use Revolution\Copilot\Types\Rpc\ManagedSettingsResolvedData;

describe('PendingManagedSettings', function () {
    it('returns the current managed settings snapshot as a typed result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.managedSettings.getCurrentSnapshot', ['sessionId' => 'session-123'])
            ->andReturn([
                'managedKeys' => ['model'],
                'serverManaged' => true,
                'source' => 'server',
            ]);

        $snapshot = (new PendingManagedSettings($client, 'session-123'))->getCurrentSnapshot();

        expect($snapshot)->toBeInstanceOf(ManagedSettingsResolvedData::class)
            ->and($snapshot->serverManaged)->toBeTrue()
            ->and($snapshot->managedKeys)->toBe(['model']);
    });

    it('returns null when no current managed settings snapshot exists', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.managedSettings.getCurrentSnapshot', ['sessionId' => 'session-123'])
            ->andReturn(null);

        $snapshot = (new PendingManagedSettings($client, 'session-123'))->getCurrentSnapshot();

        expect($snapshot)->toBeNull();
    });

    it('waits for managed settings application and publication', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.managedSettings.awaitApplied', ['sessionId' => 'session-123'])
            ->andReturn(null);
        $client->shouldReceive('request')
            ->once()
            ->with('session.managedSettings.awaitPublished', ['sessionId' => 'session-123'])
            ->andReturn(null);

        $pending = new PendingManagedSettings($client, 'session-123');

        $pending->awaitApplied();
        $pending->awaitPublished();
    });
});
