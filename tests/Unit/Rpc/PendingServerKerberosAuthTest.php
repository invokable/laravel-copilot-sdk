<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerKerberosAuth;
use Revolution\Copilot\Types\Rpc\KerberosAuthSetProviderRequest;

describe('PendingServerKerberosAuth compatibility alias', function () {
    it('registers the Kerberos callback through the network RPC', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('network.setProvider', [
                'scope' => 'runtime',
                'authenticateNegotiate' => (object) [],
            ])
            ->andReturnNull();

        (new PendingServerKerberosAuth($client))->setProvider(
            new KerberosAuthSetProviderRequest(mode: 'always', scope: 'runtime'),
        );

        expect((new ReflectionMethod(PendingServerKerberosAuth::class, 'setProvider'))->getReturnType()->getName())
            ->toBe('void');
    });

    it('accepts legacy array params and omits the optional scope', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('network.setProvider', ['authenticateNegotiate' => (object) []])
            ->andReturnNull();

        (new PendingServerKerberosAuth($client))->setProvider(['mode' => 'fallback']);
    });
});
