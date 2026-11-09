<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\NetworkScope;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerNetwork;
use Revolution\Copilot\Types\Rpc\NetworkSetProviderRequest;

describe('PendingServerNetwork', function () {
    it('registers the configured callbacks and scope', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('network.setProvider', [
                'scope' => 'runtime',
                'resolveProxy' => (object) [],
                'authenticateNegotiate' => (object) [],
            ])
            ->andReturnNull();

        (new PendingServerNetwork($client))->setProvider(new NetworkSetProviderRequest(
            scope: NetworkScope::Runtime,
            resolveProxy: true,
            authenticateNegotiate: true,
        ));
    });

    it('accepts array params and omits unconfigured callbacks', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('network.setProvider', ['scope' => 'connection'])
            ->andReturnNull();

        (new PendingServerNetwork($client))->setProvider(['scope' => 'connection']);
    });
});
