<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerRuntime;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\RuntimeOpenBrowserRequest;

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

it('opens an authorization URL through the runtime browser policy', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('runtime.openBrowser', ['url' => 'https://github.com/login/oauth/authorize'])
        ->andReturn([]);

    $runtime = (new ServerRpc($client))->runtime();

    expect($runtime)->toBeInstanceOf(PendingServerRuntime::class);

    $runtime->openBrowser(new RuntimeOpenBrowserRequest(
        url: 'https://github.com/login/oauth/authorize',
    ));
});
