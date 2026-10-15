<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Support\CanvasError;
use Revolution\Copilot\Support\CanvasHandler;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\CanvasDeclaration;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\CanvasAction;
use Revolution\Copilot\Types\Rpc\CanvasProviderCloseRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderInvokeActionRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenResult;
use Revolution\Copilot\Types\SessionConfig;

function startClientWithCanvasHandlers(
    JsonRpcClient $rpc,
    array &$handlers,
    array &$requests,
    array $responses = [],
): Client {
    $processManager = Mockery::mock(ProcessManager::class);
    $processManager->shouldReceive('start')->once();
    $processManager->shouldReceive('getStdioTransport')
        ->once()
        ->andReturn(Mockery::mock(StdioTransport::class));

    app()->bind(ProcessManager::class, fn () => $processManager);
    app()->bind(JsonRpcClient::class, fn () => $rpc);

    $rpc->shouldReceive('start')->once();
    $rpc->shouldReceive('setNotificationHandler')->once();
    $rpc->shouldReceive('setRequestHandler')
        ->times(29)
        ->andReturnUsing(function (string $method, Closure $handler) use (&$handlers): void {
            $handlers[$method] = $handler;
        });
    $rpc->shouldReceive('request')
        ->andReturnUsing(function (string $method, array $params) use (&$requests, $responses): array {
            $requests[$method][] = $params;
            $response = $responses[$method] ?? null;

            if ($response instanceof Throwable) {
                throw $response;
            }

            if (is_callable($response)) {
                return $response($params);
            }

            return match ($method) {
                'connect' => ['version' => '', 'protocolVersion' => Protocol::version()],
                'session.create', 'session.resume' => ['sessionId' => $params['sessionId']],
                'session.detach' => ['success' => true],
                default => [],
            };
        });

    $client = new Client;
    $client->start();

    return $client;
}

describe('canvas provider reverse RPC', function () {
    it('sends declaration metadata and handles create-session provider requests', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];
        $client = startClientWithCanvasHandlers($rpc, $handlers, $requests);
        $canvasHandler = new class extends CanvasHandler
        {
            public bool $closed = false;

            public function open(CanvasProviderOpenRequest $request): CanvasProviderOpenResult|array
            {
                return new CanvasProviderOpenResult(
                    url: 'https://example.test/canvas/'.$request->instanceId,
                    title: 'Editor',
                );
            }

            public function invoke(CanvasProviderInvokeActionRequest $request): mixed
            {
                if ($request->actionName !== 'format') {
                    throw CanvasError::noHandler();
                }

                return ['formatted' => $request->input];
            }

            public function close(CanvasProviderCloseRequest $request): void
            {
                $this->closed = true;
            }
        };
        $session = $client->createSession(new SessionConfig(
            sessionId: 'canvas-create',
            onPermissionRequest: PermissionHandler::approveAll(),
            canvases: [new CanvasDeclaration(
                id: 'editor',
                displayName: 'Editor',
                description: 'Edits files.',
                actions: [new CanvasAction(name: 'format')],
            )],
            canvasHandler: $canvasHandler,
        ));
        $request = [
            'sessionId' => 'canvas-create',
            'extensionId' => 'example:editor',
            'canvasId' => 'editor',
            'instanceId' => 'instance-1',
            'input' => ['file' => 'README.md'],
        ];
        $opened = $handlers['canvas.open']($request);
        $handlers['canvas.close']($request);
        $result = $handlers['canvas.action.invoke']([
            ...$request,
            'actionName' => 'format',
            'input' => ['style' => 'compact'],
        ]);

        expect($requests['session.create'][0]['canvases'])->toBe([[
            'id' => 'editor',
            'displayName' => 'Editor',
            'description' => 'Edits files.',
            'actions' => [['name' => 'format']],
        ]])
            ->and($requests['session.create'][0])->not->toHaveKey('canvasHandler')
            ->and($opened)->toBe([
                'url' => 'https://example.test/canvas/instance-1',
                'title' => 'Editor',
            ])
            ->and($canvasHandler->closed)->toBeTrue()
            ->and($result)->toBe(['formatted' => ['style' => 'compact']]);

        $error = null;

        try {
            $handlers['canvas.action.invoke']([...$request, 'actionName' => 'missing']);
        } catch (JsonRpcException $exception) {
            $error = $exception;
        }

        expect($error)->toBeInstanceOf(JsonRpcException::class)
            ->and($error->data)->toBe([
                'code' => 'canvas_action_no_handler',
                'message' => 'No handler implemented for this canvas action',
            ]);
    });

    it('registers a resume-session provider and restores open canvas state', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];
        $client = startClientWithCanvasHandlers($rpc, $handlers, $requests, [
            'session.resume' => fn (array $params): array => [
                'sessionId' => $params['sessionId'],
                'openCanvases' => [[
                    'canvasId' => 'editor',
                    'extensionId' => 'example:editor',
                    'instanceId' => 'instance-resumed',
                ]],
            ],
        ]);
        $canvasHandler = new class extends CanvasHandler
        {
            public function open(CanvasProviderOpenRequest $request): CanvasProviderOpenResult|array
            {
                return ['status' => 'ready'];
            }
        };
        $session = $client->resumeSession('canvas-resume', new ResumeSessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            canvases: [new CanvasDeclaration(
                id: 'editor',
                displayName: 'Editor',
                description: 'Edits files.',
            )],
            canvasHandler: $canvasHandler,
        ));

        expect($requests['session.resume'][0]['canvases'][0]['id'])->toBe('editor')
            ->and($handlers['canvas.open']([
                'sessionId' => 'canvas-resume',
                'extensionId' => 'example:editor',
                'canvasId' => 'editor',
                'instanceId' => 'instance-2',
            ]))->toBe(['status' => 'ready'])
            ->and($session->openCanvases()[0]->instanceId)->toBe('instance-resumed');
    });

    it('rejects missing handlers and unknown canvas ids before dispatch', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];
        $client = startClientWithCanvasHandlers($rpc, $handlers, $requests);
        $session = $client->createSession(new SessionConfig(
            sessionId: 'canvas-without-handler',
            onPermissionRequest: PermissionHandler::approveAll(),
            canvases: [new CanvasDeclaration(
                id: 'editor',
                displayName: 'Editor',
                description: 'Edits files.',
            )],
        ));

        expect(fn () => $handlers['canvas.open']([
            'sessionId' => $session->id(),
            'extensionId' => 'example:editor',
            'canvasId' => 'editor',
            'instanceId' => 'instance-1',
        ]))->toThrow(RuntimeException::class, 'No canvas handler registered for session: canvas-without-handler');

        $registered = $client->createSession(new SessionConfig(
            sessionId: 'canvas-with-handler',
            onPermissionRequest: PermissionHandler::approveAll(),
            canvases: [new CanvasDeclaration(
                id: 'editor',
                displayName: 'Editor',
                description: 'Edits files.',
            )],
            canvasHandler: new class extends CanvasHandler
            {
                public function open(CanvasProviderOpenRequest $request): CanvasProviderOpenResult|array
                {
                    return [];
                }
            },
        ));

        expect(fn () => $handlers['canvas.open']([
            'sessionId' => $registered->id(),
            'extensionId' => 'example:editor',
            'canvasId' => 'unknown',
            'instanceId' => 'instance-1',
        ]))->toThrow(RuntimeException::class, 'No canvas registered with id "unknown"')
            ->and(fn () => $handlers['canvas.open']([
                'sessionId' => 'missing-session',
                'extensionId' => 'example:editor',
                'canvasId' => 'editor',
                'instanceId' => 'instance-1',
            ]))->toThrow(RuntimeException::class, 'No canvas handler registered for session: missing-session');
    });

    it('releases the handler after the session disconnects', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $handlers = [];
        $requests = [];
        $client = startClientWithCanvasHandlers($rpc, $handlers, $requests);
        $session = $client->createSession(new SessionConfig(
            sessionId: 'canvas-disconnect',
            onPermissionRequest: PermissionHandler::approveAll(),
            canvases: [new CanvasDeclaration(
                id: 'editor',
                displayName: 'Editor',
                description: 'Edits files.',
            )],
            canvasHandler: new class extends CanvasHandler
            {
                public function open(CanvasProviderOpenRequest $request): CanvasProviderOpenResult|array
                {
                    return [];
                }
            },
        ));

        $session->disconnect();

        expect(fn () => $handlers['canvas.open']([
            'sessionId' => 'canvas-disconnect',
            'extensionId' => 'example:editor',
            'canvasId' => 'editor',
            'instanceId' => 'instance-1',
        ]))->toThrow(RuntimeException::class, 'No canvas handler registered for session: canvas-disconnect');
    });
});
