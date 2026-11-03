<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Facade;
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
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\AutoModeSwitchRequest;
use Revolution\Copilot\Types\ExitPlanModeRequest;
use Revolution\Copilot\Types\ExitPlanModeResult;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateHostRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateResult;
use Revolution\Copilot\Types\SessionConfig;

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
                'session.create' => ['sessionId' => 'created-session'],
                'session.resume' => ['sessionId' => $request['params']['sessionId']],
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
