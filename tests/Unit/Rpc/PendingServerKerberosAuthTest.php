<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerKerberosAuth;
use Revolution\Copilot\Types\Rpc\KerberosAuthSetProviderRequest;

describe('PendingServerKerberosAuth', function () {
    it('registers the Kerberos provider with the configured scope', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('kerberosAuth.setProvider', [
                'mode' => 'always',
                'scope' => 'runtime',
            ])
            ->andReturnNull();

        (new PendingServerKerberosAuth($client))->setProvider(
            new KerberosAuthSetProviderRequest(mode: 'always', scope: 'runtime'),
        );

        expect((new ReflectionMethod(PendingServerKerberosAuth::class, 'setProvider'))->getReturnType()->getName())
            ->toBe('void');
    });

    it('accepts array params and omits the optional scope', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('kerberosAuth.setProvider', ['mode' => 'fallback'])
            ->andReturnNull();

        (new PendingServerKerberosAuth($client))->setProvider(['mode' => 'fallback']);
    });
});
