<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingPermissions;
use Revolution\Copilot\Rpc\PendingPermissionsAdditionalDirectories;
use Revolution\Copilot\Types\Rpc\PermissionsAdditionalDirectoriesListResult;

it('lists additional model-context directories for the session', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->with('session.permissions.additionalDirectories.list', ['sessionId' => 'session-1'])
        ->once()
        ->andReturn(['directories' => ['/workspace/shared', '/workspace/docs']]);

    $directories = (new PendingPermissions($client, 'session-1'))->additionalDirectories();
    $result = $directories->list();

    expect($directories)->toBeInstanceOf(PendingPermissionsAdditionalDirectories::class)
        ->and($result)->toBeInstanceOf(PermissionsAdditionalDirectoriesListResult::class)
        ->and($result->directories)->toBe(['/workspace/shared', '/workspace/docs']);
});
