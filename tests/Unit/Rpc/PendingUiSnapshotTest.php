<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingUi;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\SessionUiSnapshotResult;

describe('PendingUi snapshot', function () {
    it('returns the typed snapshot for the current session', function () {
        $data = [
            'startupPrompts' => ['Welcome'],
            'pendingUserInputRequests' => [],
            'pendingElicitationRequests' => [],
            'pendingExitPlanModeRequests' => [],
            'serviceTasks' => [],
            'initializingServiceCount' => 0,
            'sidekickBackgroundTasks' => [],
            'agentTurnActive' => false,
            'processing' => false,
            'supportsMcpApps' => true,
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.ui.snapshot', ['sessionId' => 'session-1'])
            ->andReturn($data);

        $result = (new SessionRpc($client, 'session-1'))->ui()->snapshot();

        expect($result)->toBeInstanceOf(SessionUiSnapshotResult::class)
            ->and($result->startupPrompts)->toBe(['Welcome'])
            ->and($result->supportsMcpApps)->toBeTrue();
    });

    it('propagates snapshot failures', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.ui.snapshot', ['sessionId' => 'session-1'])
            ->andThrow(new JsonRpcException(-32000, 'Snapshot failed'));

        (new PendingUi($client, 'session-1'))->snapshot();
    })->throws(JsonRpcException::class, 'Snapshot failed');
});
