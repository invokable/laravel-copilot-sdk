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

it('round trips a managed settings read error', function () {
    $result = ManagedSettingsReadResult::fromArray([
        'errorMessage' => 'settings file is invalid',
    ]);

    expect($result->toArray())->toBe([
        'errorMessage' => 'settings file is invalid',
    ]);
});
