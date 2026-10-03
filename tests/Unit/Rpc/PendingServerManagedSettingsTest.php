<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerManagedSettings;
use Revolution\Copilot\Types\Rpc\ManagedSettingsReadResult;

it('reads managed settings without a session', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('managedSettings.read', [])
        ->andReturn([
            'settingsJson' => ['permissions' => ['deny' => ['Shell(rm *)']]],
        ]);

    $result = (new PendingServerManagedSettings($client))->read();

    expect($result)->toBeInstanceOf(ManagedSettingsReadResult::class)
        ->and($result->settingsJson)->toBe(['permissions' => ['deny' => ['Shell(rm *)']]])
        ->and($result->errorMessage)->toBeNull();
});

it('exposes managed-settings preview operations', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')->once()->with('managedSettings.resolve', ['selectionId' => 'selection-1'])
        ->andReturn(['resolved' => ['permissions' => ['deny' => ['Shell(rm *)']]]]);
    $client->shouldReceive('request')->once()->with('managedSettings.schema', [])
        ->andReturn(['schema' => ['type' => 'object'], 'runtimeVersion' => '1.0.92']);
    $client->shouldReceive('request')->once()->with('managedSettings.validate', ['content' => ['permissions' => []]])
        ->andReturn(['valid' => true]);
    $client->shouldReceive('request')->once()->with('managedSettings.compose', ['layers' => [['source' => 'device', 'settings' => []]]])
        ->andReturn(['resolved' => ['permissions' => []]]);

    $pending = new PendingServerManagedSettings($client);

    expect($pending->resolve(['selectionId' => 'selection-1'])->resolved)->toBe(['permissions' => ['deny' => ['Shell(rm *)']]])
        ->and($pending->schema()->runtimeVersion)->toBe('1.0.92')
        ->and($pending->validate(['content' => ['permissions' => []]])->valid)->toBeTrue()
        ->and($pending->compose(['layers' => [['source' => 'device', 'settings' => []]]])->resolved)->toBe(['permissions' => []]);
});

it('round trips a managed settings read error', function () {
    $result = ManagedSettingsReadResult::fromArray([
        'errorMessage' => 'settings file is invalid',
    ]);

    expect($result->toArray())->toBe([
        'errorMessage' => 'settings file is invalid',
    ]);
});
