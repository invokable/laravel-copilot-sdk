<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSandboxProxyCa;
use Revolution\Copilot\Types\Rpc\SandboxConfig;

describe('PendingServerSandboxProxyCa', function () {
    it('checks proxy CA relevance with array and typed sandbox configuration', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.checkRelevance', ['sandboxConfig' => ['enabled' => true]])
            ->andReturn(true);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.checkRelevance', ['sandboxConfig' => ['enabled' => false]])
            ->andReturn(false);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.checkInstallationSupport', [])
            ->andReturn(true);

        $pending = new PendingServerSandboxProxyCa($client);

        expect($pending->checkRelevance(['enabled' => true]))->toBeTrue()
            ->and($pending->checkRelevance(new SandboxConfig(enabled: false)))->toBeFalse()
            ->and($pending->checkInstallationSupport())->toBeTrue();
    });
});
