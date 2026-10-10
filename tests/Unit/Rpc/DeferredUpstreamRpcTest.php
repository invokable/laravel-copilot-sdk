<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingHost;
use Revolution\Copilot\Rpc\PendingQuota;
use Revolution\Copilot\Rpc\PendingTools;
use Revolution\Copilot\Rpc\PendingUsage;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsPermissionsEvaluateRequest;
use Revolution\Copilot\Types\Rpc\ModelClearStartupSeedRequest;
use Revolution\Copilot\Types\Rpc\PendingExternalToolRequestList;
use Revolution\Copilot\Types\Rpc\SessionPluginsRetryManagedRequest;
use Revolution\Copilot\Types\Rpc\SessionQuotaGetResult;
use Revolution\Copilot\Types\Rpc\SessionQuotaRefreshResult;
use Revolution\Copilot\Types\Rpc\UsageSetCodeChangesRequest;

it('exposes host catalog and managed permission RPCs with typed results', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('host.listSessions', ['hostId' => 'host-1'])
        ->andReturn(['sessions' => []]);
    $client->shouldReceive('request')
        ->once()
        ->with('managedSettings.permissions.evaluate', [
            'context' => ['failClosed' => true],
            'operations' => [['url' => 'https://example.com', 'kind' => 'url']],
        ])
        ->andReturn(['results' => [], 'failClosed' => true]);

    $server = new ServerRpc($client);
    $host = $server->host();
    $permissions = $server->managedSettings()->evaluatePermissions(
        new ManagedSettingsPermissionsEvaluateRequest(
            context: ['failClosed' => true],
            operations: [['url' => 'https://example.com', 'kind' => 'url']],
        ),
    );

    expect($host)->toBeInstanceOf(PendingHost::class)
        ->and($host->listSessions(new HostListSessionsRequest('host-1'))->sessions)->toBeEmpty()
        ->and($permissions->failClosed)->toBeTrue();
});

it('exposes managed plugin retry and pending external tool RPCs', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('session.plugins.retryManaged', [
            'plugins' => ['org/plugin@marketplace'],
            'sessionId' => 'session-1',
        ])
        ->andReturn(['plugins' => [['spec' => 'org/plugin@marketplace', 'status' => 'installed']]]);
    $client->shouldReceive('request')
        ->once()
        ->with('session.tools.listPendingRequests', ['sessionId' => 'session-1'])
        ->andReturn(['items' => [[
            'requestId' => 'request-1',
            'toolCallId' => 'call-1',
            'toolName' => 'lookup',
            'arguments' => ['query' => 'status'],
            'agentId' => 'subagent-1',
        ]]]);

    $session = new SessionRpc($client, 'session-1');
    $retry = $session->plugins()->retryManaged(new SessionPluginsRetryManagedRequest(['org/plugin@marketplace']));
    $pending = $session->tools()->listPendingRequests();

    expect($retry->plugins[0]->status->value)->toBe('installed')
        ->and($pending)->toBeInstanceOf(PendingExternalToolRequestList::class)
        ->and($pending->items[0]->agentId)->toBe('subagent-1');
});

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

it('clears a matching startup model seed through the internal typed RPC', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('session.model.clearStartupSeed', [
            'expectedModel' => 'gpt-5',
            'expectedProviderId' => 'loki',
            'sessionId' => 'session-1',
        ])
        ->andReturn(['cleared' => true]);

    $result = (new SessionRpc($client, 'session-1'))->model()->clearStartupSeed(
        new ModelClearStartupSeedRequest('gpt-5', 'loki'),
    );

    expect($result->cleared)->toBeTrue();
});
