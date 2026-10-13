<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerRuntime;
use Revolution\Copilot\Rpc\ServerRpc;

it('shuts down the runtime with a ten-second timeout', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('runtime.shutdown', [], 10.0)
        ->andReturn([]);

    $runtime = (new ServerRpc($client))->runtime();

    expect($runtime)->toBeInstanceOf(PendingServerRuntime::class);

    $runtime->shutdown();
});
