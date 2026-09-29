<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\DiscoveredExtensionMode;
use Revolution\Copilot\Enums\EntraTokenInteraction;
use Revolution\Copilot\Enums\EventsReadDirection;
use Revolution\Copilot\Enums\InstallationDecision;
use Revolution\Copilot\Enums\PermissionResponseCapability;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Enums\SandboxConfigSource;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingAgent;
use Revolution\Copilot\Rpc\PendingHistory;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Rpc\PendingServerExtensions;
use Revolution\Copilot\Rpc\PendingServerAccounts;
use Revolution\Copilot\Rpc\PendingSessionAccounts;
use Revolution\Copilot\Types\Hooks\UserPromptTransformedHookInput;
use Revolution\Copilot\Types\Hooks\UserPromptTransformedHookOutput;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\AgentSetPromptRequest;
use Revolution\Copilot\Types\Rpc\AuthReadValue;
use Revolution\Copilot\Types\Rpc\ConnectClientInfo;
use Revolution\Copilot\Types\Rpc\EventLogReadRequest;
use Revolution\Copilot\Types\Rpc\EntraTokenAcquireRequest;
use Revolution\Copilot\Types\Rpc\InstallationConfirmationRequest;
use Revolution\Copilot\Types\Rpc\HistoryClearContextResult;
use Revolution\Copilot\Types\Rpc\McpOauthAuthenticationStateChangedRequest;
use Revolution\Copilot\Types\Rpc\ModelMessage;
use Revolution\Copilot\Types\Rpc\QueuePendingItemsResult;
use Revolution\Copilot\Types\Rpc\SettableTokenAuthInfo;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;
use Revolution\Copilot\Types\SessionHooks;

test('session configs serialize the latest official options', function () {
    $config = new SessionConfig(
        enableExperimentalMode: true,
        additionalDirectories: ['/tmp/shared'],
        reasoningEffort: ReasoningEffort::MAX,
    );

    expect($config->toArray())
        ->toHaveKey('enableExperimentalMode', true)
        ->toHaveKey('additionalDirectories', ['/tmp/shared'])
        ->toHaveKey('reasoningEffort', 'max');

    $resumed = ResumeSessionConfig::fromArray([
        'enableExperimentalMode' => false,
        'additionalDirectories' => ['/tmp/workspace'],
    ]);

    expect($resumed->toArray())
        ->toHaveKey('enableExperimentalMode', false)
        ->toHaveKey('additionalDirectories', ['/tmp/workspace']);

    $provider = fn (array $request): array => [
        'kind' => 'token',
        'accessToken' => 'short-lived',
        'expiresIn' => 3600,
    ];
    $withAuth = new SessionConfig(
        includedBuiltinSkills: ['review'],
        gitHubTokenProvider: $provider,
    );

    expect($withAuth->includedBuiltinSkills)->toBe(['review'])
        ->and($withAuth->gitHubTokenProvider)->toBe($provider)
        ->and($withAuth->toArray())->toHaveKey('includedBuiltinSkills', ['review'])
        ->and($withAuth->toArray())->not->toHaveKey('gitHubTokenProvider');
});

test('installation confirmation reviews preserve the official challenge contract', function () {
    $request = InstallationConfirmationRequest::fromArray([
        'policySessionId' => 'session-1',
        'confirmationId' => 'challenge-1',
        'operationId' => 'operation-1',
        'expiresAt' => '2026-09-29T00:00:00Z',
        'reviewFingerprint' => 'fingerprint-1',
        'review' => ['kind' => 'skill'],
    ]);

    expect($request->toArray())->toMatchArray([
        'policySessionId' => 'session-1',
        'confirmationId' => 'challenge-1',
        'operationId' => 'operation-1',
        'expiresAt' => '2026-09-29T00:00:00Z',
        'reviewFingerprint' => 'fingerprint-1',
        'review' => ['kind' => 'skill'],
    ]);

    expect($request->decision(InstallationDecision::CONFIRM)->toArray())->toBe([
        'confirmationId' => 'challenge-1',
        'reviewFingerprint' => 'fingerprint-1',
        'decision' => 'confirm',
    ]);
});

test('latest sandbox and model change discriminators are available', function () {
    expect(SandboxConfigSource::SESSION_FLAG->value)->toBe('session_flag')
        ->and(\Revolution\Copilot\Enums\ModelChangeSource::AUTO_TIER_RECOMMENDATION->value)
        ->toBe('auto_tier_recommendation');
});

test('latest authentication, telemetry, queue, and model metadata round trip', function () {
    expect(SettableTokenAuthInfo::fromArray([
        'host' => 'github.com',
        'token' => 'token',
    ])->toArray())->toBe([
        'type' => 'token',
        'host' => 'github.com',
        'token' => 'token',
    ]);

    expect(new ConnectClientInfo(editorName: 'VS Code', editorVersion: '1.0')->toArray())
        ->toBe(['editorName' => 'VS Code', 'editorVersion' => '1.0']);

    $model = ModelInfo::fromArray([
        'id' => 'gpt-5',
        'name' => 'GPT-5',
        'capabilities' => ['supports' => [], 'limits' => []],
        'warningText' => ['dataRetention' => '30 days'],
        'infoMessages' => [['code' => 'info', 'message' => 'Fast']],
    ]);

    expect($model->warningText?->dataRetention)->toBe('30 days')
        ->and($model->infoMessages[0])->toBeInstanceOf(ModelMessage::class)
        ->and((new QueuePendingItemsResult(inFlightSteeringCount: 2))->toArray())
        ->toBe(['items' => [], 'steeringMessages' => [], 'inFlightSteeringCount' => 2]);
});

test('new permission capability and model call finished event are represented', function () {
    expect(PermissionResponseCapability::INTERACTIVE->value)->toBe('interactive');

    $event = SessionEvent::fromArray([
        'id' => 'event-1',
        'timestamp' => '2026-01-01T00:00:00Z',
        'type' => 'model.call_finished',
        'data' => ['model' => 'gpt-5'],
    ]);

    expect($event->type)->toBe(SessionEventType::MODEL_CALL_FINISHED)
        ->and($event->data)->toBe(['model' => 'gpt-5']);
});

test('session hooks expose transformed prompt callbacks', function () {
    $hook = fn (array $input): array => ['modifiedTransformedPrompt' => strtoupper($input['transformedPrompt'])];
    $hooks = SessionHooks::fromArray(['onUserPromptTransformed' => $hook]);

    expect($hooks->onUserPromptTransformed)->toBe($hook)
        ->and($hooks->toArray())->toHaveKey('onUserPromptTransformed', $hook);

    expect(UserPromptTransformedHookInput::fromArray([
        'sessionId' => 'session-1',
        'timestamp' => 123,
        'cwd' => '/tmp',
        'prompt' => 'Hello',
        'transformedPrompt' => 'Hello with context',
    ])->toArray())->toMatchArray([
        'sessionId' => 'session-1',
        'timestamp' => 123,
        'cwd' => '/tmp',
        'prompt' => 'Hello',
        'transformedPrompt' => 'Hello with context',
    ]);

    expect(UserPromptTransformedHookOutput::fromArray([
        'modifiedTransformedPrompt' => 'Updated',
    ])->toArray())->toBe([
        'modifiedTransformedPrompt' => 'Updated',
    ]);
});

test('event log reads support backward direction and agent filters', function () {
    $request = new EventLogReadRequest(
        agentIds: ['subagent-1'],
        direction: EventsReadDirection::BACKWARD,
    );

    expect($request->toArray())->toBe([
        'agentIds' => ['subagent-1'],
        'direction' => 'backward',
    ]);
});

test('new history, agent, and MCP RPC methods use official method names', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('session.history.clearContext', [
            'prompt' => 'Start over',
            'sessionId' => 'session-1',
        ])
        ->andReturn(['messagesCleared' => 4]);
    $client->shouldReceive('request')
        ->once()
        ->with('session.agent.setPrompt', [
            'id' => 'reviewer',
            'prompt' => 'Review carefully',
            'sessionId' => 'session-1',
        ])
        ->andReturn([]);
    $client->shouldReceive('request')
        ->once()
        ->with('session.mcp.oauth.authenticationStateChanged', [
            'serverName' => 'github',
            'refreshSessionToken' => true,
            'sessionId' => 'session-1',
        ])
        ->andReturn([]);

    $history = new PendingHistory($client, 'session-1');
    $agent = new PendingAgent($client, 'session-1');
    $mcp = new PendingMcp($client, 'session-1');

    $result = $history->clearContext(['prompt' => 'Start over']);

    expect($result)
        ->toBeInstanceOf(HistoryClearContextResult::class)
        ->and($result->messagesCleared)->toBe(4);

    $agent->setPrompt(new AgentSetPromptRequest('reviewer', 'Review carefully'));
    $mcp->authenticationStateChanged(new McpOauthAuthenticationStateChangedRequest('github', true));
});

test('session accounts RPC maps the 1.0.90 account and login methods', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')->once()->with('session.accounts.enumerate', [
        'sessionId' => 'session-1', 'query' => ['kind' => 'accounts'],
    ])->andReturn(['kind' => 'accounts', 'items' => [[
        'host' => 'github.com', 'login' => 'octocat', 'kind' => 'githubDotCom', 'active' => true, 'selectionId' => 'a1',
    ]]]);
    $client->shouldReceive('request')->once()->with('session.accounts.login.advance', [
        'sessionId' => 'session-1', 'flowId' => 'flow-1', 'input' => 'github.com',
    ])->andReturn(['kind' => 'completed', 'result' => ['status' => 'completed']]);

    $accounts = new PendingSessionAccounts($client, 'session-1');
    expect($accounts->enumerate()[0]->login)->toBe('octocat')
        ->and($accounts->advance(['flowId' => 'flow-1', 'input' => 'github.com'])->kind->value)->toBe('completed');

    $client->shouldReceive('request')->once()->with('session.accounts.get', [
        'sessionId' => 'session-1', 'query' => ['kind' => 'lastErrors'],
    ])->andReturn(['kind' => 'lastErrors', 'errors' => [['message' => 'Expired', 'githubMessage' => 'Token expired']]]);

    $read = $accounts->get('lastErrors');
    expect($read)->toBeInstanceOf(AuthReadValue::class)
        ->and($read->errors[0]->githubMessage)->toBe('Token expired');
});

test('session events preserve new workflow correlation fields', function () {
    $event = SessionEvent::fromArray([
        'id' => 'event-1', 'timestamp' => '2026-09-29T00:00:00Z', 'type' => 'workflow.run_updated',
        'data' => ['workflowRunId' => 'run-1', 'parentToolCallId' => 'tool-1', 'activeWorkflowSummary' => 'Running'],
    ]);

    expect($event->workflowRunId())->toBe('run-1')
        ->and($event->parentToolCallId())->toBe('tool-1')
        ->and($event->toArray()['data'])->toHaveKey('activeWorkflowSummary', 'Running');
});

test('server account broker RPC maps the official Entra token request', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')->once()->with('accounts.acquireEntraToken', [
        'clientId' => 'client-1',
        'tenantId' => 'organizations',
        'redirectUri' => 'app://callback',
        'scopes' => ['scope.read'],
        'interaction' => 'silent',
    ])->andReturn(['status' => 'interaction-required']);

    $result = (new PendingServerAccounts($client))->acquireEntraToken(new EntraTokenAcquireRequest(
        clientId: 'client-1',
        tenantId: 'organizations',
        redirectUri: 'app://callback',
        scopes: ['scope.read'],
        interaction: EntraTokenInteraction::SILENT,
    ));

    expect($result->status)->toBe('interaction-required');
});

test('server extension RPCs discover and persist enablement', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('extensions.discover', [])
        ->andReturn([
            'mode' => 'load_only',
            'extensions' => [],
        ]);
    $client->shouldReceive('request')
        ->once()
        ->with('extensions.enable', ['ids' => ['user:demo']])
        ->andReturn([]);
    $client->shouldReceive('request')
        ->once()
        ->with('extensions.disable', ['ids' => ['plugin:demo']])
        ->andReturn([]);

    $extensions = new PendingServerExtensions($client);

    expect($extensions->discover()->mode)->toBe(DiscoveredExtensionMode::LOAD_ONLY);
    $extensions->enable(['ids' => ['user:demo']]);
    $extensions->disable(['ids' => ['plugin:demo']]);
});
