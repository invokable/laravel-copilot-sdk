<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Revolt\EventLoop;
use Revolution\Copilot\Client;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\Enums\ContextTier;
use Revolution\Copilot\Enums\PermissionMode;
use Revolution\Copilot\Enums\UIAutoModeSwitchResponse;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\JsonRpc\JsonRpcMessage;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\AutoModeSwitchRequest;
use Revolution\Copilot\Types\ExitPlanModeRequest;
use Revolution\Copilot\Types\ExitPlanModeResult;
use Revolution\Copilot\Types\MemoryConfiguration;
use Revolution\Copilot\Types\MemoryStoreCallOptions;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateHostRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateResult;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\StoredMemory;

beforeEach(function () {
    Facade::clearResolvedInstances();
});

describe('Client reverse requests', function () {
    it('returns exit plan and auto mode handler results for created and resumed sessions', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $exitHandlerCalls = 0;
        $eventTypes = [];
        $exitHandler = function (ExitPlanModeRequest $request) use (&$exitHandlerCalls): ExitPlanModeResult {
            $exitHandlerCalls++;

            return new ExitPlanModeResult(
                approved: false,
                selectedAction: $request->recommendedAction,
                feedback: 'Revise the plan',
            );
        };
        $autoHandler = fn (AutoModeSwitchRequest $request) => UIAutoModeSwitchResponse::YesAlways;
        $config = new SessionConfig(
            onPermissionRequest: fn () => true,
            onExitPlanModeRequest: $exitHandler,
            onAutoModeSwitchRequest: $autoHandler,
            onEvent: function ($event) use (&$eventTypes): void {
                $eventTypes[] = $event->type->value;
            },
        );
        $createdSession = $client->createSession($config);
        $exitParams = [
            'summary' => 'A proposed plan',
            'actions' => ['approve', 'revise'],
            'recommendedAction' => 'revise',
        ];
        $transport->injectNotification('session.event', [
            'sessionId' => $createdSession->id(),
            'event' => [
                'type' => 'exit_plan_mode.requested',
                'data' => ['requestId' => 'broadcast-exit', ...$exitParams],
            ],
        ]);
        $resumedSession = $client->resumeSession(
            'resumed-session',
            new ResumeSessionConfig(
                onPermissionRequest: fn () => true,
                onExitPlanModeRequest: $exitHandler,
                onAutoModeSwitchRequest: $autoHandler,
            ),
        );

        $transport->injectRequest('exit-created', 'exitPlanMode.request', [
            ...$exitParams,
            'sessionId' => $createdSession->id(),
        ]);
        $transport->injectRequest('auto-resumed', 'autoModeSwitch.request', [
            'sessionId' => $resumedSession->id(),
            'errorCode' => 'rate_limited',
        ]);

        expect($transport->responseFor('exit-created')['result'])->toBe([
            'approved' => false,
            'selectedAction' => 'revise',
            'feedback' => 'Revise the plan',
        ])
            ->and($transport->responseFor('auto-resumed')['result'])->toBe(['response' => 'yes_always'])
            ->and($exitHandlerCalls)->toBe(1)
            ->and($eventTypes)->toBe(['exit_plan_mode.requested'])
            ->and(array_column($transport->messages, 'method'))->not->toContain('session.handleExitPlanMode');
    });

    it('returns defaults without handlers and JSON-RPC errors for invalid requests', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
        ));
        $nullResponseSession = $client->resumeSession(
            'null-response-session',
            new ResumeSessionConfig(
                onPermissionRequest: fn () => true,
                onAutoModeSwitchRequest: fn () => null,
            ),
        );
        $validExitParams = [
            'sessionId' => $session->id(),
            'summary' => 'A proposed plan',
            'actions' => ['approve'],
            'recommendedAction' => 'approve',
        ];

        $transport->injectRequest('exit-default', 'exitPlanMode.request', $validExitParams);
        $transport->injectRequest('auto-default', 'autoModeSwitch.request', [
            'sessionId' => $session->id(),
        ]);
        $transport->injectRequest('auto-null-response', 'autoModeSwitch.request', [
            'sessionId' => $nullResponseSession->id(),
        ]);
        $transport->injectRequest('exit-invalid', 'exitPlanMode.request', [
            'sessionId' => $session->id(),
            'summary' => 'A proposed plan',
            'actions' => 'approve',
            'recommendedAction' => 'approve',
        ]);
        $transport->injectRequest('exit-unknown', 'exitPlanMode.request', [
            ...$validExitParams,
            'sessionId' => 'missing-session',
        ]);

        expect($transport->responseFor('exit-default')['result'])->toBe(['approved' => true])
            ->and($transport->responseFor('auto-default')['result'])->toBe(['response' => 'no'])
            ->and($transport->responseFor('auto-null-response')['result'])->toBe(['response' => 'no'])
            ->and($transport->responseFor('exit-invalid')['error']['message'])
            ->toBe('Invalid exit plan mode request payload')
            ->and($transport->responseFor('exit-unknown')['error']['message'])
            ->toBe('Session not found: missing-session');
    });

    it('returns the result from a registered session creation handler', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $callbackRequest = null;
        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
        ));
        $request = [
            'sessionId' => $session->id(),
            'parentSessionId' => $session->id(),
            'newSessionId' => 'session-child',
            'cwd' => '/workspace',
            'configDir' => '/config',
            'permissionMode' => 'manual',
            'contextTier' => 'long_context',
        ];
        $policy = $session->setSessionCreationHandler(
            function (SessionManagementCreateHostRequest $request) use (&$callbackRequest): SessionManagementCreateResult {
                $callbackRequest = $request;

                return new SessionManagementCreateResult(sessionId: $request->newSessionId);
            },
        );

        $transport->injectRequest('create-child', 'sessionManagement.create', $request);

        $configureRequest = array_values(array_filter(
            $transport->messages,
            fn (array $message): bool => ($message['method'] ?? null) === 'session.management.configure',
        ))[0];

        expect($policy->create)->toBeTrue()
            ->and($configureRequest['params'])->toBe([
                'hasCreateHandler' => true,
                'sessionId' => $session->id(),
            ])
            ->and($callbackRequest)->toBeInstanceOf(SessionManagementCreateHostRequest::class)
            ->and($callbackRequest->permissionMode)->toBe(PermissionMode::Manual)
            ->and($callbackRequest->contextTier)->toBe(ContextTier::LONG_CONTEXT)
            ->and($transport->responseFor('create-child')['result'])->toBe(['sessionId' => 'session-child']);
    });

    it('rejects session creation requests without a handler or from a different parent', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
        ));
        $request = [
            'sessionId' => $session->id(),
            'parentSessionId' => $session->id(),
            'newSessionId' => 'session-child',
            'cwd' => '/workspace',
            'configDir' => '/config',
            'permissionMode' => 'manual',
        ];

        $transport->injectRequest('create-no-handler', 'sessionManagement.create', [
            ...$request,
        ]);

        $callbackCalls = 0;
        $session->setSessionCreationHandler(
            function (SessionManagementCreateHostRequest $request) use (&$callbackCalls): SessionManagementCreateResult {
                $callbackCalls++;

                return new SessionManagementCreateResult(sessionId: $request->newSessionId);
            },
        );
        $transport->injectRequest('create-wrong-parent', 'sessionManagement.create', [
            ...$request,
            'parentSessionId' => 'different-parent',
        ]);

        expect($transport->responseFor('create-no-handler')['error']['message'])
            ->toBe('Session creation handler is unavailable')
            ->and($transport->responseFor('create-wrong-parent')['error']['message'])
            ->toBe('Session creation handler is unavailable')
            ->and($callbackCalls)->toBe(0);
    });

    it('does not expose session creation callback exception details', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
        ));
        $session->setSessionCreationHandler(function (): never {
            throw new RuntimeException('Sensitive host detail');
        });

        $transport->injectRequest('create-failed', 'sessionManagement.create', [
            'sessionId' => $session->id(),
            'parentSessionId' => $session->id(),
            'newSessionId' => 'session-child',
            'cwd' => '/workspace',
            'configDir' => '/config',
            'permissionMode' => 'manual',
        ]);

        expect($transport->responseFor('create-failed')['error']['message'])
            ->toBe('Session creation callback failed')
            ->not->toContain('Sensitive host detail');
    });

    it('registers app memory stores for created and resumed sessions', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $createdCall = null;
        $resumedCall = null;
        $createdSession = $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
            memory: new MemoryConfiguration(
                enabled: true,
                storeMemory: function (StoredMemory $memory, MemoryStoreCallOptions $options) use (&$createdCall): void {
                    $createdCall = [$memory, $options];
                },
            ),
        ));
        $resumedSession = $client->resumeSession(
            'resumed-session',
            new ResumeSessionConfig(
                onPermissionRequest: fn () => true,
                memory: new MemoryConfiguration(
                    enabled: true,
                    storeMemory: function (StoredMemory $memory, MemoryStoreCallOptions $options) use (&$resumedCall): void {
                        $resumedCall = [$memory, $options];
                    },
                ),
            ),
        );
        $memoryData = [
            'subject' => 'Project conventions',
            'fact' => 'Use readonly data types.',
            'citations' => 'src/Types/SessionEvent.php:55',
            'reason' => 'Preserves immutable payload behavior.',
        ];

        $createRequest = array_values(array_filter(
            $transport->messages,
            fn (array $message): bool => ($message['method'] ?? null) === 'session.create',
        ))[0];
        $resumeRequest = array_values(array_filter(
            $transport->messages,
            fn (array $message): bool => ($message['method'] ?? null) === 'session.resume',
        ))[0];

        $transport->injectRequest('memory-created', 'memoryStore.store', [
            ...$memoryData,
            'sessionId' => $createdSession->id(),
        ]);
        $transport->injectRequest('memory-resumed', 'memoryStore.store', [
            ...$memoryData,
            'sessionId' => $resumedSession->id(),
        ]);

        expect($createRequest['params']['hasMemoryStore'])->toBeTrue()
            ->and($createRequest['params']['memory'])->toBe(['enabled' => true])
            ->and($resumeRequest['params']['hasMemoryStore'])->toBeTrue()
            ->and($resumeRequest['params']['memory'])->toBe(['enabled' => true])
            ->and($createdCall[0])->toBeInstanceOf(StoredMemory::class)
            ->and($createdCall[0]->toArray())->toBe(['sessionId' => $createdSession->id(), ...$memoryData])
            ->and($createdCall[1]->signal)->toBeInstanceOf(CancellationToken::class)
            ->and($resumedCall[0])->toBeInstanceOf(StoredMemory::class)
            ->and($resumedCall[0]->toArray())->toBe(['sessionId' => $resumedSession->id(), ...$memoryData])
            ->and($resumedCall[1]->signal)->toBeInstanceOf(CancellationToken::class)
            ->and($transport->responseFor('memory-created')['result'])->toBe([])
            ->and($transport->responseFor('memory-resumed')['result'])->toBe([]);
    });

    it('supports callbacks in array memory configuration', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $storeCalls = 0;
        $session = $client->createSession([
            'onPermissionRequest' => fn () => true,
            'memory' => [
                'enabled' => true,
                'storeMemory' => function (StoredMemory $memory, MemoryStoreCallOptions $options) use (&$storeCalls): void {
                    $storeCalls++;
                },
            ],
        ]);
        $request = array_values(array_filter(
            $transport->messages,
            fn (array $message): bool => ($message['method'] ?? null) === 'session.create',
        ))[0];

        $transport->injectRequest('memory-array', 'memoryStore.store', [
            'sessionId' => $session->id(),
            'subject' => 'Project conventions',
            'fact' => 'Use readonly data types.',
            'citations' => 'src/Types/SessionEvent.php:55',
            'reason' => 'Preserves immutable payload behavior.',
        ]);

        expect($request['params']['hasMemoryStore'])->toBeTrue()
            ->and($request['params']['memory'])->toBe(['enabled' => true])
            ->and($transport->responseFor('memory-array')['result'])->toBe([])
            ->and($storeCalls)->toBe(1);
    });

    it('omits memory store advertisement when no callback is configured', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
            memory: new MemoryConfiguration(enabled: true),
        ));
        $client->resumeSession(
            'resumed-session',
            new ResumeSessionConfig(
                onPermissionRequest: fn () => true,
                memory: ['enabled' => true],
            ),
        );

        $createRequest = array_values(array_filter(
            $transport->messages,
            fn (array $message): bool => ($message['method'] ?? null) === 'session.create',
        ))[0];
        $resumeRequest = array_values(array_filter(
            $transport->messages,
            fn (array $message): bool => ($message['method'] ?? null) === 'session.resume',
        ))[0];

        expect($createRequest['params'])->not->toHaveKey('hasMemoryStore')
            ->and($resumeRequest['params'])->not->toHaveKey('hasMemoryStore');
    });

    it('requires enabled memory and rejects memory stores for cloud sessions', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $storeMemory = fn () => null;

        expect(fn () => $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
            memory: new MemoryConfiguration(enabled: false, storeMemory: $storeMemory),
        )))->toThrow(InvalidArgumentException::class, 'memory.storeMemory requires memory.enabled to be true.')
            ->and(fn () => $client->resumeSession('disabled-memory-session', [
                'onPermissionRequest' => fn () => true,
                'memory' => [
                    'enabled' => false,
                    'storeMemory' => $storeMemory,
                ],
            ]))->toThrow(InvalidArgumentException::class, 'memory.storeMemory requires memory.enabled to be true.')
            ->and(fn () => $client->createSession([
                'onPermissionRequest' => fn () => true,
                'memory' => [
                    'enabled' => true,
                    'storeMemory' => $storeMemory,
                ],
                'cloud' => [],
            ]))->toThrow(InvalidArgumentException::class, 'Memory stores are not supported for cloud sessions.')
            ->and(array_column($transport->messages, 'method'))->not->toContain('session.create', 'session.resume');
    });

    it('returns a generic error and logs callback details when memory storage fails', function () {
        Log::spy();
        [$client, $transport] = createReverseRpcTestClient();
        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
            memory: new MemoryConfiguration(
                enabled: true,
                storeMemory: fn () => throw new RuntimeException('Sensitive host detail'),
            ),
        ));

        $transport->injectRequest('memory-failed', 'memoryStore.store', [
            'sessionId' => $session->id(),
            'subject' => 'Project conventions',
            'fact' => 'Use readonly data types.',
            'citations' => 'src/Types/SessionEvent.php:55',
            'reason' => 'Preserves immutable payload behavior.',
        ]);

        expect($transport->responseFor('memory-failed')['error']['message'])
            ->toBe('Memory store callback failed')
            ->not->toContain('Sensitive host detail');

        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn (string $message, array $context): bool => $message === 'Memory store callback failed'
                && ($context['exception'] ?? null) instanceof RuntimeException
                && $context['exception']->getMessage() === 'Sensitive host detail',
        );
    });

    it('discards a memory store result when the runtime cancels the request', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $signal = null;
        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
            memory: new MemoryConfiguration(
                enabled: true,
                storeMemory: function (StoredMemory $memory, MemoryStoreCallOptions $options) use ($transport, &$signal): void {
                    $signal = $options->signal;
                    $suspension = EventLoop::getSuspension();
                    $options->signal->onCancellationRequested(fn () => $suspension->resume());
                    EventLoop::delay(0.01, fn () => $transport->injectNotification(
                        '$/cancelRequest',
                        ['id' => 'memory-cancelled'],
                    ));
                    $suspension->suspend();
                },
            ),
        ));

        $transport->injectRequest('memory-cancelled', 'memoryStore.store', [
            'sessionId' => $session->id(),
            'subject' => 'Project conventions',
            'fact' => 'Use readonly data types.',
            'citations' => 'src/Types/SessionEvent.php:55',
            'reason' => 'Preserves immutable payload behavior.',
        ]);
        EventLoop::delay(0.05, fn () => EventLoop::getDriver()->stop());
        EventLoop::run();

        expect($signal)->toBeInstanceOf(CancellationToken::class)
            ->and($signal->isCancellationRequested())->toBeTrue()
            ->and($transport->responseFor('memory-cancelled')['error']['code'])->toBe(-32800)
            ->and($transport->responseFor('memory-cancelled')['error']['message'])->toContain('Memory store call cancelled');
    });

    it('clears the memory callback when the session disconnects', function () {
        [$client, $transport] = createReverseRpcTestClient();
        $calls = 0;
        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: fn () => true,
            memory: new MemoryConfiguration(
                enabled: true,
                storeMemory: function () use (&$calls): void {
                    $calls++;
                },
            ),
        ));
        $session->disconnect();

        $transport->injectRequest('memory-after-disconnect', 'memoryStore.store', [
            'sessionId' => $session->id(),
            'subject' => 'Project conventions',
            'fact' => 'Use readonly data types.',
            'citations' => 'src/Types/SessionEvent.php:55',
            'reason' => 'Preserves immutable payload behavior.',
        ]);

        expect($transport->responseFor('memory-after-disconnect')['error']['message'])
            ->toBe('Memory store handler is unavailable')
            ->and($calls)->toBe(0);
    });
});

/**
 * @return array{Client, object}
 */
function createReverseRpcTestClient(): array
{
    $transport = new class implements Transport
    {
        /**
         * @var list<array<string, mixed>>
         */
        public array $messages = [];

        protected ?Closure $receiveHandler = null;

        public function start(): void {}

        public function stop(): void {}

        public function onReceive(Closure $handler): void
        {
            $this->receiveHandler = $handler;
        }

        public function send(string $message): void
        {
            $content = substr($message, strpos($message, "\r\n\r\n") + 4);
            $request = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
            $this->messages[] = $request;

            if (! isset($request['method'])) {
                return;
            }

            $result = match ($request['method']) {
                'connect' => ['protocolVersion' => Protocol::version()],
                'session.create' => ['sessionId' => $request['params']['sessionId'] ?? 'created-session'],
                'session.resume' => ['sessionId' => $request['params']['sessionId']],
                'session.detach' => ['success' => true],
                'session.management.configure' => [
                    'enabled' => true,
                    'scope' => 'all',
                    'list' => true,
                    'create' => true,
                    'message' => false,
                    'close' => false,
                ],
                default => [],
            };

            EventLoop::queue(function () use ($request, $result): void {
                ($this->receiveHandler)(JsonRpcMessage::response($request['id'], $result)->toJson());
            });
        }

        public function injectRequest(string $id, string $method, array $params): void
        {
            ($this->receiveHandler)(JsonRpcMessage::request($id, $method, $params)->toJson());
        }

        public function injectNotification(string $method, array $params): void
        {
            ($this->receiveHandler)(JsonRpcMessage::notification($method, $params)->toJson());
        }

        public function responseFor(string $id): ?array
        {
            foreach (array_reverse($this->messages) as $message) {
                if (($message['id'] ?? null) === $id) {
                    return $message;
                }
            }

            return null;
        }
    };

    $stdioTransport = Mockery::mock(StdioTransport::class);
    $processManager = Mockery::mock(ProcessManager::class);
    $processManager->shouldReceive('start')->once();
    $processManager->shouldReceive('getStdioTransport')->andReturn($stdioTransport);

    app()->bind(ProcessManager::class, fn () => $processManager);
    app()->bind(JsonRpcClient::class, fn () => new JsonRpcClient($transport));

    $client = new Client;
    $client->start();

    return [$client, $transport];
}
