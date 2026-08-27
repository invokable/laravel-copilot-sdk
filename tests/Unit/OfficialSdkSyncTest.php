<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\DiscoveredExtensionMode;
use Revolution\Copilot\Enums\PermissionResponseCapability;
use Revolution\Copilot\Enums\EventsReadDirection;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingAgent;
use Revolution\Copilot\Rpc\PendingHistory;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Rpc\PendingServerExtensions;
use Revolution\Copilot\Types\Hooks\UserPromptTransformedHookInput;
use Revolution\Copilot\Types\Hooks\UserPromptTransformedHookOutput;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\AgentSetPromptRequest;
use Revolution\Copilot\Types\Rpc\EventLogReadRequest;
use Revolution\Copilot\Types\Rpc\HistoryClearContextResult;
use Revolution\Copilot\Types\Rpc\McpOauthAuthenticationStateChangedRequest;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionHooks;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\Rpc\ConnectClientInfo;
use Revolution\Copilot\Types\Rpc\ModelMessage;
use Revolution\Copilot\Types\Rpc\QueuePendingItemsResult;
use Revolution\Copilot\Types\Rpc\SettableTokenAuthInfo;

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

    $event = \Revolution\Copilot\Types\SessionEvent::fromArray([
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
