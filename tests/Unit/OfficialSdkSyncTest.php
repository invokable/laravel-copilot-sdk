<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\DiscoveredExtensionMode;
use Revolution\Copilot\Enums\EventsReadDirection;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingAgent;
use Revolution\Copilot\Rpc\PendingHistory;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Rpc\PendingServerExtensions;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionHooks;
use Revolution\Copilot\Types\Hooks\UserPromptTransformedHookInput;
use Revolution\Copilot\Types\Hooks\UserPromptTransformedHookOutput;
use Revolution\Copilot\Types\Rpc\AgentSetPromptRequest;
use Revolution\Copilot\Types\Rpc\EventLogReadRequest;
use Revolution\Copilot\Types\Rpc\HistoryClearContextResult;
use Revolution\Copilot\Types\Rpc\McpOauthAuthenticationStateChangedRequest;

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
