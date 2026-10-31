<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutopilotEvaluateIdleContinuationOutcome;
use Revolution\Copilot\Enums\DiagnosticCursorStatus;
use Revolution\Copilot\Enums\QueuedCommandWaitRequirement;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingAutopilot;
use Revolution\Copilot\Rpc\PendingConnectors;
use Revolution\Copilot\Rpc\PendingCustomizations;
use Revolution\Copilot\Rpc\PendingDiagnostics;
use Revolution\Copilot\Rpc\PendingInstructions;
use Revolution\Copilot\Rpc\PendingLifecycleDiagnostics;
use Revolution\Copilot\Rpc\PendingLsp;
use Revolution\Copilot\Rpc\PendingManagedSettings;
use Revolution\Copilot\Rpc\PendingMarketplaces;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Rpc\PendingPlugins;
use Revolution\Copilot\Rpc\PendingProvider;
use Revolution\Copilot\Rpc\PendingQueue;
use Revolution\Copilot\Rpc\PendingServerGit;
use Revolution\Copilot\Rpc\PendingServerSandboxProxyCa;
use Revolution\Copilot\Rpc\PendingServerSessions;
use Revolution\Copilot\Rpc\PendingUi;
use Revolution\Copilot\Rpc\PendingWorkflow;
use Revolution\Copilot\Rpc\PendingWorkflowJournal;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\AutopilotEvaluateIdleContinuationResult;
use Revolution\Copilot\Types\Rpc\CommandsReportQueuedCommandWaitRequest;
use Revolution\Copilot\Types\Rpc\CommandsReportQueuedCommandWaitResult;
use Revolution\Copilot\Types\Rpc\CustomizationsReloadResult;
use Revolution\Copilot\Types\Rpc\LifecycleDiagnosticsReadRequest;
use Revolution\Copilot\Types\Rpc\LifecycleDiagnosticsReadResult;

describe('SessionRpc session-scoped operations', function () {
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

    it('sends internal session RPCs with typed results and bound session ids', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.autopilot.evaluateIdleContinuation', [
                'sessionId' => 'session-123',
            ])
            ->andReturn(['outcome' => 'continued']);
        $client->shouldReceive('request')
            ->once()
            ->with('session.commands.reportQueuedCommandWait', [
                'requestId' => 'request-1',
                'accountIdentity' => 'account-1',
                'requirement' => 'managed-policy-published',
                'waiting' => true,
                'sessionId' => 'session-123',
            ])
            ->andReturn(['recorded' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.diagnostics.lifecycle.read', [
                'cursor' => 'cursor-1',
                'max' => 25,
                'waitMs' => 250,
                'sessionId' => 'session-123',
            ])
            ->andReturn([
                'events' => [[
                    'data' => ['system' => 'mcp'],
                    'timestamp' => '2026-10-31T12:00:00Z',
                    'type' => 'runtime.lifecycle.system_state',
                ]],
                'cursor' => 'cursor-2',
                'cursorStatus' => 'ok',
                'hasMore' => false,
            ]);

        $session = new SessionRpc($client, 'session-123');
        $autopilotResult = $session->autopilot()->evaluateIdleContinuation();
        $commandsResult = $session->commands()->reportQueuedCommandWait(new CommandsReportQueuedCommandWaitRequest(
            requestId: 'request-1',
            accountIdentity: 'account-1',
            requirement: QueuedCommandWaitRequirement::ManagedPolicyPublished,
            waiting: true,
        ));
        $lifecycleDiagnostics = $session->diagnostics()->lifecycle();
        $diagnosticsResult = $lifecycleDiagnostics->read(new LifecycleDiagnosticsReadRequest(
            cursor: 'cursor-1',
            max: 25,
            waitMs: 250,
        ));

        expect($session->autopilot())->toBeInstanceOf(PendingAutopilot::class)
            ->and($autopilotResult)->toBeInstanceOf(AutopilotEvaluateIdleContinuationResult::class)
            ->and($autopilotResult->outcome)->toBe(AutopilotEvaluateIdleContinuationOutcome::Continued)
            ->and($commandsResult)->toBeInstanceOf(CommandsReportQueuedCommandWaitResult::class)
            ->and($commandsResult->recorded)->toBeTrue()
            ->and($lifecycleDiagnostics)->toBeInstanceOf(PendingLifecycleDiagnostics::class)
            ->and($diagnosticsResult)->toBeInstanceOf(LifecycleDiagnosticsReadResult::class)
            ->and($diagnosticsResult->cursorStatus)->toBe(DiagnosticCursorStatus::Ok)
            ->and($diagnosticsResult->events[0]->type)->toBe('runtime.lifecycle.system_state');
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

    it('returns the detailed customization reload outcome', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.customizations.reload', ['sessionId' => 'session-3'])
            ->andReturn([
                'errors' => [],
                'outcomes' => [['subsystem' => 'agents', 'status' => 'reloaded']],
                'warnings' => ['No project instructions found'],
            ]);

        $result = (new PendingCustomizations($client, 'session-3'))->reload();

        expect($result)->toBeInstanceOf(CustomizationsReloadResult::class)
            ->and($result->outcomes[0]->subsystem)->toBe('agents')
            ->and($result->warnings)->toHaveCount(1);
    });

    it('forwards new server and session-scoped method params without losing session scope', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.getStatus', ['sandboxConfig' => ['enabled' => true]])
            ->andReturn(['state' => 'present', 'canInstall' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('git.currentBranchRemote', ['cwd' => '/repo'])
            ->andReturn(['remote' => 'origin']);
        $client->shouldReceive('request')
            ->once()
            ->with('session.ui.handleHumanAskUser', ['requestId' => 'ask-1', 'sessionId' => 'session-4'])
            ->andReturn(['success' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.loadWorkspace', ['sessionId' => 'session-4', 'sessionsHome' => '/sessions'])
            ->andReturn(['workspaceJson' => '{"id":"workspace-1"}']);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.createWorkspace', [
                'sessionId' => 'session-5',
                'convention' => 'copilot',
                'context' => ['cwd' => '/repo'],
                'sessionStatePath' => '/sessions/session-5',
            ])
            ->andReturn(['workspaceJson' => '{"id":"workspace-2"}']);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.updateWorkspaceFields', [
                'sessionId' => 'session-5',
                'sessionsHome' => '/sessions',
                'fieldsJson' => '{"branch":"main"}',
            ])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.listConfigured', ['sessionId' => 'session-4'])
            ->andReturn(['servers' => []]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.setConnectedIdeInfo', [
                'ideInfo' => ['name' => 'editor'],
                'sessionId' => 'session-4',
            ])
            ->andReturn([]);

        $status = (new PendingServerSandboxProxyCa($client))->getStatus(['enabled' => true]);
        $remote = (new PendingServerGit($client))->currentBranchRemote('/repo');
        $ui = (new PendingUi($client, 'session-4'))->handleHumanAskUser(['requestId' => 'ask-1']);
        $workspace = (new PendingServerSessions($client))->loadWorkspace([
            'sessionId' => 'session-4',
            'sessionsHome' => '/sessions',
        ]);
        $sessions = new PendingServerSessions($client);
        $createdWorkspace = $sessions->createWorkspace([
            'sessionId' => 'session-5',
            'convention' => 'copilot',
            'context' => ['cwd' => '/repo'],
            'sessionStatePath' => '/sessions/session-5',
        ]);
        $sessions->updateWorkspaceFields([
            'sessionId' => 'session-5',
            'sessionsHome' => '/sessions',
            'fieldsJson' => '{"branch":"main"}',
        ]);
        $mcp = new PendingMcp($client, 'session-4');
        $configured = $mcp->listConfigured();
        $mcp->setConnectedIdeInfo(['ideInfo' => ['name' => 'editor']]);

        expect($status->state)->toBe('present')
            ->and($remote->remote)->toBe('origin')
            ->and($ui['success'])->toBeTrue()
            ->and($workspace->workspaceJson)->toBe('{"id":"workspace-1"}')
            ->and($createdWorkspace->workspaceJson)->toBe('{"id":"workspace-2"}')
            ->and($configured)->toBe(['servers' => []]);
    });
});
