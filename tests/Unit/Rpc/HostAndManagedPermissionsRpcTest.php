<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingHost;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\HostDisposeRequest;
use Revolution\Copilot\Types\Rpc\HostDisposeResult;
use Revolution\Copilot\Types\Rpc\HostGitHubEnvironmentOptions;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostPublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostStartRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsPermissionsEvaluateRequest;

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

it('starts and publishes host sessions with typed host options', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('host.start', [
            'hostId' => 'host-1',
            'computeId' => 'compute-1',
            'githubEnvironment' => [
                'name' => 'Example',
                'computeId' => 'compute-1',
                'requireConnectionBinding' => false,
            ],
        ])
        ->andReturn(['hostId' => 'host-1', 'environmentId' => 'environment-1']);
    $client->shouldReceive('request')
        ->once()
        ->with('host.publishSession', [
            'hostId' => 'host-1',
            'sessionId' => 'session-1',
            'preferResident' => true,
        ])
        ->andReturn(['sessionId' => 'session-1', 'sessionUri' => 'ahp://host/session-1']);

    $host = (new ServerRpc($client))->host();
    $started = $host->start(new HostStartRequest(
        hostId: 'host-1',
        computeId: 'compute-1',
        githubEnvironment: new HostGitHubEnvironmentOptions(
            name: 'Example',
            computeId: 'compute-1',
            requireConnectionBinding: false,
        ),
    ));
    $published = $host->publishSession(new HostPublishSessionRequest(
        hostId: 'host-1',
        sessionId: 'session-1',
        preferResident: true,
    ));

    expect($started->environmentId)->toBe('environment-1')
        ->and($published->sessionUri)->toBe('ahp://host/session-1');
});

it('disposes a host listener with typed or array parameters', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->twice()
        ->with('host.dispose', ['hostId' => 'host-1'])
        ->andReturn([]);

    $host = (new ServerRpc($client))->host();
    $typedResult = $host->dispose(new HostDisposeRequest('host-1'));
    $arrayResult = $host->dispose(['hostId' => 'host-1']);

    expect($typedResult)->toBeInstanceOf(HostDisposeResult::class)
        ->and($typedResult->toArray())->toBe([])
        ->and($arrayResult)->toBeInstanceOf(HostDisposeResult::class);
});
