<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingConnectors;
use Revolution\Copilot\Rpc\PendingCustomizations;
use Revolution\Copilot\Rpc\PendingDiagnostics;
use Revolution\Copilot\Rpc\PendingInstructions;
use Revolution\Copilot\Rpc\PendingLsp;
use Revolution\Copilot\Rpc\PendingManagedSettings;
use Revolution\Copilot\Rpc\PendingMarketplaces;
use Revolution\Copilot\Rpc\PendingPlugins;
use Revolution\Copilot\Rpc\PendingProvider;
use Revolution\Copilot\Rpc\PendingQueue;
use Revolution\Copilot\Rpc\PendingWorkflow;
use Revolution\Copilot\Rpc\PendingWorkflowJournal;
use Revolution\Copilot\Rpc\SessionRpc;

describe('new upstream session RPC groups', function () {
    it('runs a workflow with its owning session id', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.workflow.run', [
                'name' => 'weekly-summary',
                'args' => ['team' => 'platform'],
                'sessionId' => 'session-123',
            ])
            ->andReturn(['runId' => 'run-123', 'status' => 'running']);

        $result = (new PendingWorkflow($client, 'session-123'))->run([
            'name' => 'weekly-summary',
            'args' => ['team' => 'platform'],
        ]);

        expect($result)->toBe(['runId' => 'run-123', 'status' => 'running']);
    });

    it('exposes durable workflow journal operations', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.workflow.journal.get', [
                'runId' => 'run-123',
                'key' => 'checkpoint',
                'sessionId' => 'session-123',
            ])
            ->andReturn(['hit' => true, 'resultJson' => ['step' => 2]]);

        $workflow = new PendingWorkflow($client, 'session-123');

        expect($workflow->journal())->toBeInstanceOf(PendingWorkflowJournal::class)
            ->and($workflow->journal()->get(['runId' => 'run-123', 'key' => 'checkpoint']))
            ->toBe(['hit' => true, 'resultJson' => ['step' => 2]]);
    });

    it('adds the session id to connector and diagnostics requests', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.list', ['sessionId' => 'session-123'])
            ->andReturn(['connectors' => []]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.diagnostics.read', [
                'sources' => ['mcp'],
                'sessionId' => 'session-123',
            ])
            ->andReturn(['entries' => []]);

        $session = new SessionRpc($client, 'session-123');

        expect($session->connectors())->toBeInstanceOf(PendingConnectors::class)
            ->and($session->connectors()->list())->toBe(['connectors' => []])
            ->and($session->diagnostics())->toBeInstanceOf(PendingDiagnostics::class)
            ->and($session->diagnostics()->read(['sources' => ['mcp']]))->toBe(['entries' => []]);
    });

    it('exposes settings, customizations, and plugin marketplace operations', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.managedSettings.get', ['sessionId' => 'session-123'])
            ->andReturn(['available' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.customizations.reload', ['sessionId' => 'session-123'])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.marketplaces.list', ['sessionId' => 'session-123'])
            ->andReturn(['marketplaces' => []]);

        $session = new SessionRpc($client, 'session-123');

        expect($session->managedSettings())->toBeInstanceOf(PendingManagedSettings::class)
            ->and($session->managedSettings()->get())->toBe(['available' => true])
            ->and($session->customizations())->toBeInstanceOf(PendingCustomizations::class);

        $session->customizations()->reload();

        expect($session->plugins()->marketplaces())->toBeInstanceOf(PendingMarketplaces::class)
            ->and($session->plugins()->marketplaces()->list())->toBe(['marketplaces' => []]);
    });

    it('adds session scope to plugin, queue, provider, and instruction operations', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.install', [
                'name' => 'sample',
                'sessionId' => 'session-123',
            ])
            ->andReturn(['installed' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.uninstall', [
                'name' => 'sample',
                'sessionId' => 'session-123',
            ])
            ->andReturn(null);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.appendSteering', [
                'message' => 'Continue',
                'sessionId' => 'session-123',
            ])
            ->andReturn(['queued' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.provider.sync', ['sessionId' => 'session-123'])
            ->andReturn(['models' => []]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.instructions.reload', ['sessionId' => 'session-123'])
            ->andReturn([]);

        $session = new SessionRpc($client, 'session-123');

        expect($session->plugins())->toBeInstanceOf(PendingPlugins::class)
            ->and($session->plugins()->install(['name' => 'sample']))->toBe(['installed' => true])
            ->and($session->queue())->toBeInstanceOf(PendingQueue::class)
            ->and($session->queue()->appendSteering(['message' => 'Continue']))->toBe(['queued' => true])
            ->and($session->provider())->toBeInstanceOf(PendingProvider::class)
            ->and($session->provider()->sync())->toBe(['models' => []])
            ->and($session->instructions())->toBeInstanceOf(PendingInstructions::class);

        $session->plugins()->uninstall(['name' => 'sample']);
        $session->instructions()->reload();
    });

    it('supports LSP initialization and graceful session shutdown parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.lsp.initialize', [
                'workingDirectory' => '/workspace',
                'force' => true,
                'sessionId' => 'session-123',
            ])
            ->andReturn(null);
        $client->shouldReceive('request')
            ->once()
            ->with('session.shutdown', [
                'detachSessionEndHooks' => true,
                'sessionId' => 'session-123',
            ])
            ->andReturn(null);

        $session = new SessionRpc($client, 'session-123');

        expect($session->lsp())->toBeInstanceOf(PendingLsp::class);

        $session->lsp()->initialize([
            'workingDirectory' => '/workspace',
            'force' => true,
        ]);
        $session->shutdown(['detachSessionEndHooks' => true]);
    });
});
