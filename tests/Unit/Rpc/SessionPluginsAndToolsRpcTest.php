<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\PendingExternalToolRequestList;
use Revolution\Copilot\Types\Rpc\SessionPluginsRetryManagedRequest;

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
