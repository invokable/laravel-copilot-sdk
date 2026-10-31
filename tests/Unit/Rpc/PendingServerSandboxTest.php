<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSandbox;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\SandboxHostSupport;
use Revolution\Copilot\Types\Rpc\SandboxPathGrantWarningParams;
use Revolution\Copilot\Types\Rpc\SandboxPathGrantWarningResult;

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

    it('returns a runtime warning for a sandbox path grant', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.getPathGrantWarning', [
                'resolvedPath' => 'C:\\',
                'readOnly' => false,
            ])
            ->andReturn(['warning' => 'Grant a folder instead of the drive root.']);

        $result = (new ServerRpc($client))->sandbox()->getPathGrantWarning(new SandboxPathGrantWarningParams(
            resolvedPath: 'C:\\',
            readOnly: false,
        ));

        expect($result)->toBeInstanceOf(SandboxPathGrantWarningResult::class)
            ->and($result->warning)->toBe('Grant a folder instead of the drive root.');
    });
});
