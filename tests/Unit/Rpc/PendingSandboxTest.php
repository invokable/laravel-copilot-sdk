<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSandbox;
use Revolution\Copilot\Types\Rpc\SandboxEnforcementStatus;

describe('PendingSandbox', function () {
    it('returns sandbox enforcement status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.sandbox.getEnforcementStatus', ['sessionId' => 'session-abc'])
            ->andReturn([
                'required' => true,
                'blocked' => false,
            ]);

        $status = (new PendingSandbox($client, 'session-abc'))->getEnforcementStatus();

        expect($status)->toBeInstanceOf(SandboxEnforcementStatus::class)
            ->and($status->required)->toBeTrue()
            ->and($status->blocked)->toBeFalse()
            ->and($status->reason)->toBeNull();
    });
});
