<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Revolt\EventLoop;
use Revolution\Copilot\Enums\AgentMode;
use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\ModelSwitchAutoTierStatus;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Exceptions\SessionErrorException;
use Revolution\Copilot\Exceptions\SessionTimeoutException;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\Hooks\SubagentStartHookInput;
use Revolution\Copilot\Types\Hooks\SubagentStartHookOutput;
use Revolution\Copilot\Types\Hooks\SubagentStopHookInput;
use Revolution\Copilot\Types\Hooks\SubagentStopHookOutput;
use Revolution\Copilot\Types\McpAuthRequest;
use Revolution\Copilot\Types\McpAuthResult;
use Revolution\Copilot\Types\McpAuthToken;
use Revolution\Copilot\Types\Rpc\ModelSwitchAutoTierResult;
use Revolution\Copilot\Types\SessionEvent;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    // Don't use preventStrayRequests here as Session tests use mocked RPC client
});

describe('Session', function () {
    it('updates local tool handlers only after the runtime accepts replacement tools', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $originalHandler = fn () => 'original';
        $replacementHandler = fn () => 'replacement';
        $client->shouldReceive('request')
            ->once()
            ->with('session.tools.set', [
                'tools' => [[
                    'name' => 'search',
                    'description' => 'Search',
                    'parameters' => [],
                    'defer' => 'auto',
                ]],
                'sessionId' => 'session-1',
            ])
            ->andReturn([]);

        $session = new Session('session-1', $client);
        $session->registerTools([['name' => 'search', 'handler' => $originalHandler]]);
        $session->setTools([['name' => 'search', 'description' => 'Search', 'defer' => 'auto', 'handler' => $replacementHandler]]);

        expect($session->getToolHandler('search'))->toBe($replacementHandler);
    });

    it('keeps current tool handlers when the runtime rejects a replacement', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->andThrow(new RuntimeException('rejected'));
        $originalHandler = fn () => 'original';
        $session = new Session('session-1', $client);
        $session->registerTools([['name' => 'search', 'handler' => $originalHandler]]);

        expect(fn () => $session->setTools([['name' => 'search', 'handler' => fn () => 'replacement']]))
            ->toThrow(RuntimeException::class, 'rejected');
        expect($session->getToolHandler('search'))->toBe($originalHandler);
    });

    it('can be instantiated with sessionId and client', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);

        $session = new Session('test-session-id', $mockClient);

        expect($session->id())->toBe('test-session-id')
            ->and($session->sessionId)->toBe('test-session-id');
    });

    it('sends a host-provided token for MCP OAuth requests', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->with('session.mcp.oauth.handlePendingRequest', [
                'requestId' => 'oauth-request-1',
                'result' => [
                    'kind' => 'token',
                    'accessToken' => 'host-access-token',
                    'expiresIn' => 3600,
                    'tokenType' => 'Bearer',
                ],
                'sessionId' => 'session-1',
            ])
            ->once()
            ->andReturn(['success' => true]);

        $received = null;
        $session = new Session('session-1', $client);
        $session->registerMcpAuthHandler(function (McpAuthRequest $request, array $context) use (&$received): McpAuthToken {
            $received = [$request, $context];

            return new McpAuthToken('host-access-token', 'Bearer', 3600);
        });
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => [
                'requestId' => 'oauth-request-1',
                'serverName' => 'private-mcp',
                'serverUrl' => 'https://mcp.example.test',
                'reason' => 'initial',
                'wwwAuthenticateParams' => ['scope' => 'read'],
                'resourceMetadata' => '{"resource":"metadata"}',
                'staticClientConfig' => ['clientId' => 'host-client'],
            ],
        ]));

        expect($received[0])->toBeInstanceOf(McpAuthRequest::class)
            ->and($received[0]->serverName)->toBe('private-mcp')
            ->and($received[0]->wwwAuthenticateParams)->toBe(['scope' => 'read'])
            ->and($received[1])->toBe(['sessionId' => 'session-1']);
    });

    it('sends array token data for MCP OAuth requests', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->with('session.mcp.oauth.handlePendingRequest', [
                'requestId' => 'oauth-request-2',
                'result' => [
                    'kind' => 'token',
                    'accessToken' => 'array-token',
                ],
                'sessionId' => 'session-1',
            ])
            ->once()
            ->andReturn(['success' => true]);

        $session = new Session('session-1', $client);
        $session->registerMcpAuthHandler(fn () => ['accessToken' => 'array-token']);
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => ['requestId' => 'oauth-request-2'],
        ]));
    });

    it('sends token results for MCP OAuth requests', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->with('session.mcp.oauth.handlePendingRequest', [
                'requestId' => 'oauth-request-result',
                'result' => [
                    'kind' => 'token',
                    'accessToken' => 'result-token',
                    'expiresIn' => 1800,
                    'tokenType' => 'Bearer',
                ],
                'sessionId' => 'session-1',
            ])
            ->once()
            ->andReturn(['success' => true]);

        $session = new Session('session-1', $client);
        $session->registerMcpAuthHandler(fn () => new McpAuthResult(
            kind: 'token',
            accessToken: 'result-token',
            tokenType: 'Bearer',
            expiresIn: 1800,
        ));
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => ['requestId' => 'oauth-request-result'],
        ]));
    });

    it('cancels MCP OAuth requests when the handler declines', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->with('session.mcp.oauth.handlePendingRequest', [
                'requestId' => 'oauth-request-3',
                'result' => ['kind' => 'cancelled'],
                'sessionId' => 'session-1',
            ])
            ->once()
            ->andReturn(['success' => true]);

        $session = new Session('session-1', $client);
        $session->registerMcpAuthHandler(fn () => new McpAuthResult('cancelled'));
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => ['requestId' => 'oauth-request-3'],
        ]));
    });

    it('cancels MCP OAuth requests when the handler returns null', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->with('session.mcp.oauth.handlePendingRequest', [
                'requestId' => 'oauth-request-null',
                'result' => ['kind' => 'cancelled'],
                'sessionId' => 'session-1',
            ])
            ->once()
            ->andReturn(['success' => true]);

        $session = new Session('session-1', $client);
        $session->registerMcpAuthHandler(fn () => null);
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => ['requestId' => 'oauth-request-null'],
        ]));
    });

    it('cancels MCP OAuth requests when the handler throws', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->with('session.mcp.oauth.handlePendingRequest', [
                'requestId' => 'oauth-request-4',
                'result' => ['kind' => 'cancelled'],
                'sessionId' => 'session-1',
            ])
            ->once()
            ->andReturn(['success' => true]);

        $session = new Session('session-1', $client);
        $session->registerMcpAuthHandler(fn () => throw new RuntimeException('token unavailable'));
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => ['requestId' => 'oauth-request-4'],
        ]));
    });

    it('warns and does not respond when MCP OAuth has no handler', function () {
        Log::spy();
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->never();

        $session = new Session('session-1', $client);
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => ['requestId' => 'oauth-request-5'],
        ]));

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Received MCP OAuth request without a registered MCP auth handler.', [
                'sessionId' => 'session-1',
                'requestId' => 'oauth-request-5',
            ]);
    });

    it('ignores MCP OAuth events without a request ID', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->never();

        $handlerCalls = 0;
        $session = new Session('session-1', $client);
        $session->registerMcpAuthHandler(function () use (&$handlerCalls): null {
            $handlerCalls++;

            return null;
        });
        $session->dispatchEvent(SessionEvent::fromArray([
            'type' => 'mcp.oauth_required',
            'data' => ['serverName' => 'private-mcp'],
        ]));

        expect($handlerCalls)->toBe(0);
    });

    it('send dispatches session.send request', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.send', [
                'sessionId' => 'test-session',
                'prompt' => 'Hello World',
            ])
            ->once()
            ->andReturn(['messageId' => 'msg-123']);

        $session = new Session('test-session', $mockClient);
        $messageId = $session->send('Hello World');

        expect($messageId)->toBe('msg-123');
    });

    it('send passes attachments and mode', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.send', [
                'sessionId' => 'test-session',
                'prompt' => 'prompt',
                'attachments' => [['type' => 'file', 'path' => '/test.txt']],
                'mode' => 'plan',
            ])
            ->once()
            ->andReturn(['messageId' => 'msg-456']);

        $session = new Session('test-session', $mockClient);
        $messageId = $session->send('prompt', [['type' => 'file', 'path' => '/test.txt']], 'plan');

        expect($messageId)->toBe('msg-456');
    });

    it('send accepts agent mode enum', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.send', [
                'sessionId' => 'test-session',
                'prompt' => 'prompt',
                'agentMode' => 'plan',
            ])
            ->once()
            ->andReturn(['messageId' => 'msg-789']);

        $session = new Session('test-session', $mockClient);
        $messageId = $session->send('prompt', agentMode: AgentMode::PLAN);

        expect($messageId)->toBe('msg-789');
    });

    it('send accepts agent mode string', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.send', [
                'sessionId' => 'test-session',
                'prompt' => 'prompt',
                'agentMode' => 'autopilot',
            ])
            ->once()
            ->andReturn(['messageId' => 'msg-790']);

        $session = new Session('test-session', $mockClient);
        $messageId = $session->send('prompt', agentMode: 'autopilot');

        expect($messageId)->toBe('msg-790');
    });

    it('send passes source', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.send', [
                'sessionId' => 'test-session',
                'prompt' => 'prompt',
                'source' => 'agent-reviewer',
            ])
            ->once()
            ->andReturn(['messageId' => 'msg-791']);

        $session = new Session('test-session', $mockClient);
        $messageId = $session->send('prompt', source: 'agent-reviewer');

        expect($messageId)->toBe('msg-791');
    });

    it('on registers event handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $handlerCalled = false;
        $receivedEvent = null;

        $unsubscribe = $session->on(handler: function (SessionEvent $event) use (&$handlerCalled, &$receivedEvent) {
            $handlerCalled = true;
            $receivedEvent = $event;
        });

        expect($unsubscribe)->toBeCallable();

        // Dispatch an event
        $event = SessionEvent::fromArray(['type' => 'assistant.message', 'data' => ['content' => 'Hi']]);
        $session->dispatchEvent($event);

        expect($handlerCalled)->toBeTrue()
            ->and($receivedEvent)->toBe($event);
    });

    it('off removes event handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $callCount = 0;
        $handler = function () use (&$callCount) {
            $callCount++;
        };

        $session->on($handler);
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'session.idle']));

        expect($callCount)->toBe(1);

        $session->off($handler);
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'session.idle']));

        expect($callCount)->toBe(1); // Should not increase
    });

    it('unsubscribe function removes handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $callCount = 0;
        $unsubscribe = $session->on(function () use (&$callCount) {
            $callCount++;
        });

        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'session.idle']));
        expect($callCount)->toBe(1);

        $unsubscribe();
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'session.idle']));
        expect($callCount)->toBe(1); // Should not increase
    });

    it('on with event type string filters events', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $messageCallCount = 0;
        $idleCallCount = 0;

        $session->on('assistant.message', function () use (&$messageCallCount) {
            $messageCallCount++;
        });

        $session->on('session.idle', function () use (&$idleCallCount) {
            $idleCallCount++;
        });

        // Dispatch different event types
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'assistant.message', 'data' => ['content' => 'Hi']]));
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'session.idle']));
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'assistant.message_delta', 'data' => ['deltaContent' => 'partial']]));

        expect($messageCallCount)->toBe(1)
            ->and($idleCallCount)->toBe(1);
    });

    it('on with SessionEventType enum filters events', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $messageCallCount = 0;
        $receivedEvent = null;

        $session->on(SessionEventType::ASSISTANT_MESSAGE, function (SessionEvent $event) use (&$messageCallCount, &$receivedEvent) {
            $messageCallCount++;
            $receivedEvent = $event;
        });

        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'session.idle']));
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'assistant.message', 'data' => ['content' => 'Hello']]));

        expect($messageCallCount)->toBe(1)
            ->and($receivedEvent)->not->toBeNull()
            ->and($receivedEvent->content())->toBe('Hello');
    });

    it('typed on unsubscribe removes handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $callCount = 0;
        $unsubscribe = $session->on('assistant.message', function () use (&$callCount) {
            $callCount++;
        });

        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'assistant.message', 'data' => ['content' => 'Hi']]));
        expect($callCount)->toBe(1);

        $unsubscribe();
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'assistant.message', 'data' => ['content' => 'Hi again']]));
        expect($callCount)->toBe(1); // Should not increase
    });

    it('typed and wildcard handlers both receive events', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $typedCallCount = 0;
        $wildcardCallCount = 0;

        $session->on('assistant.message', function () use (&$typedCallCount) {
            $typedCallCount++;
        });

        $session->on(function () use (&$wildcardCallCount) {
            $wildcardCallCount++;
        });

        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'assistant.message', 'data' => ['content' => 'Hi']]));
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'session.idle']));

        // Typed handler only receives matching events
        expect($typedCallCount)->toBe(1);
        // Wildcard handler receives all events
        expect($wildcardCallCount)->toBe(2);
    });

    it('on throws exception when type without handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        expect(fn () => $session->on('assistant.message'))
            ->toThrow(InvalidArgumentException::class, 'Handler must be provided when specifying an event type');
    });

    it('dispatchEvent ignores handler errors', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $secondHandlerCalled = false;

        $session->on(function () {
            throw new RuntimeException('Handler error');
        });

        $session->on(function () use (&$secondHandlerCalled) {
            $secondHandlerCalled = true;
        });

        // Should not throw
        $session->dispatchEvent(SessionEvent::fromArray(['type' => 'session.idle']));

        expect($secondHandlerCalled)->toBeTrue();
    });

    it('invokes post tool use failure hook', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $called = false;
        $receivedInput = null;
        $receivedContext = null;

        $session->registerHooks([
            'onPostToolUseFailure' => function ($input, $context) use (&$called, &$receivedInput, &$receivedContext) {
                $called = true;
                $receivedInput = $input;
                $receivedContext = $context;

                return ['additionalContext' => 'Prefer a safer command'];
            },
        ]);

        $result = $session->handleHooksInvoke('postToolUseFailure', [
            'toolName' => 'bash',
            'error' => 'failed',
        ]);

        expect($called)->toBeTrue()
            ->and($receivedInput)->toBe(['toolName' => 'bash', 'error' => 'failed'])
            ->and($receivedContext)->toBe(['sessionId' => 'test-session'])
            ->and($result)->toBe(['additionalContext' => 'Prefer a safer command']);
    });

    describe('error hook payload normalization', function () {
        it('passes the message from error objects to both error hooks', function (string $hookType) {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $session = new Session('test-session', $mockClient);
            $receivedInput = null;
            $hookName = $hookType === 'errorOccurred' ? 'onErrorOccurred' : 'onSessionEnd';

            $session->registerHooks([
                $hookName => function (array $input) use (&$receivedInput): null {
                    $receivedInput = $input;

                    return null;
                },
            ]);

            $session->handleHooksInvoke($hookType, [
                'error' => ['name' => 'Error', 'message' => 'Request failed'],
                'reason' => 'error',
            ]);

            expect($receivedInput)->toBe([
                'error' => 'Request failed',
                'reason' => 'error',
            ]);
        })->with(['errorOccurred', 'sessionEnd']);

        it('serializes error objects without a string message', function (string $hookType, array $error, string $expected) {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $session = new Session('test-session', $mockClient);
            $receivedInput = null;
            $hookName = $hookType === 'errorOccurred' ? 'onErrorOccurred' : 'onSessionEnd';

            $session->registerHooks([
                $hookName => function (array $input) use (&$receivedInput): null {
                    $receivedInput = $input;

                    return null;
                },
            ]);

            $session->handleHooksInvoke($hookType, ['error' => $error]);

            expect($receivedInput)->toBe(['error' => $expected]);
        })->with([
            'errorOccurred with no message' => ['errorOccurred', ['name' => 'Error'], '{"name":"Error"}'],
            'sessionEnd with no message' => ['sessionEnd', ['name' => 'Error'], '{"name":"Error"}'],
            'errorOccurred with a non-string message' => ['errorOccurred', ['message' => 500], '{"message":500}'],
            'sessionEnd with a non-string message' => ['sessionEnd', ['message' => 500], '{"message":500}'],
        ]);

        it('leaves non-object errors unchanged for both error hooks', function (string $hookType, mixed $error) {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $session = new Session('test-session', $mockClient);
            $receivedInput = null;
            $hookName = $hookType === 'errorOccurred' ? 'onErrorOccurred' : 'onSessionEnd';

            $session->registerHooks([
                $hookName => function (array $input) use (&$receivedInput): null {
                    $receivedInput = $input;

                    return null;
                },
            ]);

            $session->handleHooksInvoke($hookType, ['error' => $error]);

            expect($receivedInput)->toBe(['error' => $error]);
        })->with([
            'errorOccurred string' => ['errorOccurred', 'Request failed'],
            'sessionEnd null' => ['sessionEnd', null],
            'errorOccurred list' => ['errorOccurred', ['first', 'second']],
            'sessionEnd integer' => ['sessionEnd', 500],
        ]);

        it('leaves error objects unchanged for other hook types', function () {
            $mockClient = Mockery::mock(JsonRpcClient::class);
            $session = new Session('test-session', $mockClient);
            $receivedInput = null;
            $input = ['error' => ['name' => 'Error', 'message' => 'Request failed']];

            $session->registerHooks([
                'onPostToolUseFailure' => function (array $input) use (&$receivedInput): null {
                    $receivedInput = $input;

                    return null;
                },
            ]);

            $session->handleHooksInvoke('postToolUseFailure', $input);

            expect($receivedInput)->toBe($input);
        });
    });

    it('invokes the sub-agent start hook with typed input and serializes its output', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('parent-session', $mockClient);
        $receivedInput = null;
        $receivedContext = null;

        $session->registerHooks([
            'onSubagentStart' => function (SubagentStartHookInput $input, array $context) use (&$receivedInput, &$receivedContext): SubagentStartHookOutput {
                $receivedInput = $input;
                $receivedContext = $context;

                return new SubagentStartHookOutput(additionalContext: 'Follow repository conventions.');
            },
        ]);

        $result = $session->handleHooksInvoke('subagentStart', [
            'sessionId' => 'parent-session',
            'timestamp' => 1706600000,
            'cwd' => '/workspace',
            'transcriptPath' => '/tmp/agent.jsonl',
            'agentName' => 'reviewer',
        ]);

        expect($receivedInput)->toBeInstanceOf(SubagentStartHookInput::class)
            ->and($receivedInput->agentName)->toBe('reviewer')
            ->and($receivedContext)->toBe(['sessionId' => 'parent-session'])
            ->and($result)->toBe(['additionalContext' => 'Follow repository conventions.']);
    });

    it('invokes the sub-agent stop hook with typed input and serializes a response rewrite', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('parent-session', $mockClient);
        $receivedInput = null;

        $session->registerHooks([
            'onSubagentStop' => function (SubagentStopHookInput $input) use (&$receivedInput): SubagentStopHookOutput {
                $receivedInput = $input;

                return new SubagentStopHookOutput(modifiedResponse: 'Reviewed and ready.');
            },
        ]);

        $result = $session->handleHooksInvoke('subagentStop', [
            'sessionId' => 'parent-session',
            'timestamp' => 1706600000,
            'cwd' => '/workspace',
            'transcriptPath' => '/tmp/agent.jsonl',
            'agentName' => 'reviewer',
            'agentType' => 'general-purpose',
            'stopReason' => 'end_turn',
            'response' => 'Review complete',
        ]);

        expect($receivedInput)->toBeInstanceOf(SubagentStopHookInput::class)
            ->and($receivedInput->response)->toBe('Review complete')
            ->and($result)->toBe(['modifiedResponse' => 'Reviewed and ready.']);
    });

    it('registerTools stores tool handlers', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $handlerA = fn () => 'result A';
        $handlerB = fn () => 'result B';

        $session->registerTools([
            ['name' => 'toolA', 'handler' => $handlerA],
            ['name' => 'toolB', 'handler' => $handlerB],
        ]);

        expect($session->getToolHandler('toolA'))->toBe($handlerA)
            ->and($session->getToolHandler('toolB'))->toBe($handlerB)
            ->and($session->getToolHandler('toolC'))->toBeNull();
    });

    it('getEvents returns session events', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.getMessages', ['sessionId' => 'test-session'])
            ->once()
            ->andReturn([
                'events' => [
                    ['type' => 'user.message', 'data' => ['content' => 'Hello']],
                    ['type' => 'assistant.message', 'data' => ['content' => 'Hi there']],
                ],
            ]);

        $session = new Session('test-session', $mockClient);
        $messages = $session->getEvents();

        expect($messages)->toHaveCount(2)
            ->and($messages[0])->toBeInstanceOf(SessionEvent::class)
            ->and($messages[0]->type->value)->toBe('user.message')
            ->and($messages[1]->type())->toBe('assistant.message');
    });

    it('disconnect sends detach request and clears handlers', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.detach', ['sessionId' => 'test-session'])
            ->once()
            ->andReturn(['success' => true]);

        $session = new Session('test-session', $mockClient);

        // Register a handler
        $session->on(fn () => null);
        $session->registerTools([['name' => 'test', 'handler' => fn () => null]]);
        $session->registerPermissionHandler(fn () => ['kind' => 'approved']);

        $session->disconnect();

        // Handlers should be cleared
        expect($session->getToolHandler('test'))->toBeNull();
    });

    it('abort sends abort request', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.abort', ['sessionId' => 'test-session'])
            ->once()
            ->andReturn([]);

        $session = new Session('test-session', $mockClient);
        $session->abort();
    });

    it('disconnect is idempotent', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.detach', ['sessionId' => 'test-session'])
            ->once()
            ->andReturn(['success' => true]);

        $session = new Session('test-session', $mockClient);
        $session->disconnect();
        $session->disconnect(); // Second call should be no-op
    });

    it('disconnect throws and does not clear handlers when the runtime reports failure', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.detach', ['sessionId' => 'test-session'])
            ->twice()
            ->andReturn(['success' => false, 'error' => 'Connection lost']);

        $session = new Session('test-session', $mockClient);
        $session->registerTools([['name' => 'test', 'handler' => fn () => null]]);

        expect(fn () => $session->disconnect())->toThrow(RuntimeException::class);

        // Handlers remain registered since the detach never succeeded
        expect($session->getToolHandler('test'))->not->toBeNull();
    });

    it('setModel calls session.model.switchTo via rpc', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.model.switchTo', [
                'modelId' => 'claude-sonnet-4',
                'sessionId' => 'test-session',
            ])
            ->once()
            ->andReturn(['modelId' => 'claude-sonnet-4']);

        $session = new Session('test-session', $mockClient);
        $session->setModel('claude-sonnet-4');
    });

    it('setModel passes autoTier through to session.model.switchTo', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.model.switchTo', [
                'modelId' => 'auto',
                'sessionId' => 'test-session',
                'autoTier' => 'balance',
            ])
            ->once()
            ->andReturn(['modelId' => 'auto']);

        $session = new Session('test-session', $mockClient);
        $session->setModel('auto', autoTier: AutoTier::BALANCE);
    });

    it('setAutoTier calls session.model.switchAutoTier via rpc', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.model.switchAutoTier', [
                'autoTier' => 'intelligence',
                'sessionId' => 'test-session',
            ])
            ->once()
            ->andReturn(['status' => 'pending', 'effectiveAutoTier' => 'intelligence']);

        $session = new Session('test-session', $mockClient);
        $result = $session->setAutoTier(AutoTier::INTELLIGENCE);

        expect($result)->toBeInstanceOf(ModelSwitchAutoTierResult::class)
            ->and($result->status)->toBe(ModelSwitchAutoTierStatus::PENDING);
    });

    it('handlePermissionRequest uses registered handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->registerPermissionHandler(fn (array $request, array $context) => [
            'kind' => 'approved',
            'tool' => $request['toolName'],
        ]);

        $result = $session->handlePermissionRequest(['toolName' => 'bash']);

        expect($result)->toBe(['kind' => 'approved', 'tool' => 'bash']);
    });

    it('handlePermissionRequest returns denied when no handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $result = $session->handlePermissionRequest(['toolName' => 'bash']);

        expect($result)->toBe(['kind' => 'user-not-available']);
    });

    it('handlePermissionRequest returns denied on handler error', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->registerPermissionHandler(function () {
            throw new RuntimeException('Handler error');
        });

        $result = $session->handlePermissionRequest(['toolName' => 'bash']);

        expect($result)->toBe(['kind' => 'user-not-available']);
    });

    it('handlePermissionRequest passes managedSettingsEnabled through the invocation context', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient, managedSettingsEnabled: true);

        $receivedInvocation = null;
        $session->registerPermissionHandler(function (array $request, array $invocation) use (&$receivedInvocation) {
            $receivedInvocation = $invocation;

            return ['kind' => 'approve-once'];
        });

        $session->handlePermissionRequest(['toolName' => 'bash']);

        expect($receivedInvocation)->toBe([
            'sessionId' => 'test-session',
            'managedSettingsEnabled' => true,
        ]);
    });

    it('handlePermissionRequest unwraps an attributed handler result', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->registerPermissionHandler(fn (array $request, array $invocation) => [
            'kind' => 'attributed',
            'result' => ['kind' => 'approve-once'],
            'decisionContext' => [
                'outcome' => 'auto_approved',
                'source' => 'judge_recommendation',
                'surface' => 'sdk',
            ],
        ]);

        $result = $session->handlePermissionRequest(['toolName' => 'bash']);

        expect($result)->toBe(['kind' => 'approve-once']);
    });

    it('sendAndWait returns event with exception on session error', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.send', Mockery::any())
            ->andReturn(['messageId' => 'msg-123']);

        $session = new Session('test-session', $mockClient);

        $receivedEvent = null;
        $session->on(function (SessionEvent $event) use (&$receivedEvent) {
            if ($event->failed() && $event->errorMessage() === 'Test error') {
                $receivedEvent = $event;
            }
        });

        // Simulate error event after a short delay
        EventLoop::delay(0.02, function () use ($session) {
            $errorEvent = SessionEvent::fromArray([
                'type' => 'session.error',
                'data' => ['message' => 'Test error'],
            ]);
            $session->dispatchEvent($errorEvent);
        });

        $result = $session->sendAndWait('Hello', timeout: 0.2);

        expect($receivedEvent)->not->toBeNull()
            ->and($receivedEvent->failed())->toBeTrue()
            ->and($receivedEvent->errorMessage())->toBe('Test error');

        // throw() should throw the stored exception
        expect(fn () => $receivedEvent->throw())
            ->toThrow(SessionErrorException::class, 'Session error: Test error');
    });

    it('sendAndWait returns event with exception on timeout', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.send', Mockery::any())
            ->andReturn(['messageId' => 'msg-123']);

        // No events dispatched - timeout will occur
        $session = new Session('test-session', $mockClient);

        $receivedEvent = null;
        $session->on(function (SessionEvent $event) use (&$receivedEvent) {
            if ($event->failed()) {
                $receivedEvent = $event;
            }
        });

        $result = $session->sendAndWait('Hello', timeout: 0.1);

        expect($receivedEvent)->not->toBeNull()
            ->and($receivedEvent->failed())->toBeTrue()
            ->and($receivedEvent->errorMessage())->toContain('timed out');

        // throw() should throw the stored exception
        expect(fn () => $receivedEvent->throw())
            ->toThrow(SessionTimeoutException::class);
    });

    it('successful event throw() returns self', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.message',
            'data' => ['content' => 'Hello'],
        ]);

        $result = $event->throw();

        expect($result)->toBe($event);
    });
});
