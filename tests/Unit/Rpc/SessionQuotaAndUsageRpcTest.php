<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingQuota;
use Revolution\Copilot\Rpc\PendingUsage;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\SessionQuotaGetResult;
use Revolution\Copilot\Types\Rpc\SessionQuotaRefreshResult;
use Revolution\Copilot\Types\Rpc\UsageSetCodeChangesRequest;

it('reads, refreshes, and drains session quota and sends absolute code-change totals', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('session.quota.get', ['sessionId' => 'session-1'])
        ->andReturn(['snapshots' => []]);
    $client->shouldReceive('request')
        ->once()
        ->with('session.quota.refresh', ['sessionId' => 'session-1'])
        ->andReturn(['snapshots' => []]);
    $client->shouldReceive('request')
        ->once()
        ->with('session.quota.takeWarnings', ['sessionId' => 'session-1'])
        ->andReturn([['warningType' => 'quota-low', 'message' => 'Quota is low']]);
    $client->shouldReceive('request')
        ->once()
        ->with('session.usage.setCodeChanges', [
            'linesAdded' => 12,
            'linesRemoved' => 3,
            'filesCount' => 2,
            'sessionId' => 'session-1',
        ])
        ->andReturn(null);

    $session = new SessionRpc($client, 'session-1');
    $quota = $session->quota();
    $usage = $session->usage();
    $warnings = $quota->takeWarnings();
    $usage->setCodeChanges(new UsageSetCodeChangesRequest(12, 3, 2));

    expect($quota)->toBeInstanceOf(PendingQuota::class)
        ->and($quota->get())->toBeInstanceOf(SessionQuotaGetResult::class)
        ->and($quota->refresh())->toBeInstanceOf(SessionQuotaRefreshResult::class)
        ->and($warnings[0]->warningType)->toBe('quota-low')
        ->and($usage)->toBeInstanceOf(PendingUsage::class);
});
