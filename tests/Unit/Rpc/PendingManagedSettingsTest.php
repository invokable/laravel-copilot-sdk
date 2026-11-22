<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingManagedSettings;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\ManagedSettingsResolvedData;

describe('PendingManagedSettings', function () {
    it('refreshes managed settings for the session and returns the resolved snapshot', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.managedSettings.refresh', ['sessionId' => 'session-123'])
            ->andReturn([
                'bypassPermissionsDisabled' => false,
                'deviceManaged' => true,
                'failClosed' => false,
                'managedKeys' => ['model'],
                'serverManaged' => false,
                'settings' => ['model' => 'example-model'],
                'source' => 'device',
            ]);

        $snapshot = (new SessionRpc($client, 'session-123'))->managedSettings()->refresh();

        expect($snapshot)->toBeInstanceOf(ManagedSettingsResolvedData::class)
            ->and($snapshot->deviceManaged)->toBeTrue()
            ->and($snapshot->managedKeys)->toBe(['model'])
            ->and($snapshot->settings)->toBe(['model' => 'example-model'])
            ->and($snapshot->source)->toBe('device');
    });

    it('propagates refresh failures rather than returning retained settings', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.managedSettings.refresh', ['sessionId' => 'session-123'])
            ->andThrow(new JsonRpcException(-32000, 'Policy refresh failed'));

        (new PendingManagedSettings($client, 'session-123'))->refresh();
    })->throws(JsonRpcException::class, 'Policy refresh failed');

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
