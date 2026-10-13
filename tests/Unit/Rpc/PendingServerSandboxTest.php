<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSandbox;
use Revolution\Copilot\Types\Rpc\SandboxHostSupport;

describe('PendingServerSandbox', function () {
    it('returns host sandbox support and capabilities', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.getHostSupport', [])
            ->andReturn([
                'supported' => true,
                'capabilities' => [
                    ['name' => 'network', 'supported' => false, 'reason' => 'install helper'],
                ],
            ]);

        $result = (new PendingServerSandbox($client))->getHostSupport();

        expect($result)->toBeInstanceOf(SandboxHostSupport::class)
            ->and($result->supported)->toBeTrue()
            ->and($result->reason)->toBeNull()
            ->and($result->capabilities[0]->name)->toBe('network')
            ->and($result->capabilities[0]->supported)->toBeFalse()
            ->and($result->capabilities[0]->reason)->toBe('install helper');
    });
});
