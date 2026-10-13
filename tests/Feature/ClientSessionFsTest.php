<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\SessionFsBinaryProvider;
use Revolution\Copilot\Contracts\SessionFsProvider;
use Revolution\Copilot\CopilotManager;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Protocol;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderCapabilities;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionFsConfig;

/**
 * Starts a Client (or CopilotManager client) against a mocked runtime and records
 * every registered request handler and outgoing request.
 */
function startSessionFsClient(array $options, array &$handlers, array &$requests, array $responses = [], ?Closure $start = null): mixed
{
    $processManager = Mockery::mock(ProcessManager::class);
    $processManager->shouldReceive('start')->once();
    $processManager->shouldReceive('getStdioTransport')->andReturn(Mockery::mock(StdioTransport::class));
    $processManager->shouldReceive('stop')->zeroOrMoreTimes()->andReturnUsing(function () use (&$requests): void {
        $requests['__process.stop'] = ($requests['__process.stop'] ?? 0) + 1;
    });

    $rpc = Mockery::mock(JsonRpcClient::class);
    $rpc->shouldReceive('start')->once();
    $rpc->shouldReceive('stop')->zeroOrMoreTimes()->andReturnUsing(function () use (&$requests): void {
        $requests['__rpc.stop'] = ($requests['__rpc.stop'] ?? 0) + 1;
    });
    $rpc->shouldReceive('setNotificationHandler')->once();
    $rpc->shouldReceive('setRequestHandler')
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

            return match ($method) {
                'connect' => ['version' => '', 'protocolVersion' => Protocol::version()],
                'session.create', 'session.resume' => ['sessionId' => $params['sessionId']],
                'sessionFs.setProvider' => ['success' => true],
                'session.detach' => ['success' => true],
                default => [],
            };
        });

    app()->bind(ProcessManager::class, fn () => $processManager);
    app()->bind(JsonRpcClient::class, fn () => $rpc);

    return $start !== null ? $start($options) : tap(new Client($options))->start();
}

function sessionFsOptions(array $override = []): array
{
    return array_replace([
        'initialCwd' => '/workspace',
        'sessionStatePath' => '/state',
        'conventions' => 'posix',
    ], $override);
}

function sessionFsTestFactoryProvider(): SessionFsProvider
{
    return new class implements SessionFsProvider
    {
        public function readFile(string $path): string
        {
            return "read:{$path}";
        }

        public function writeFile(string $path, string $content, ?int $mode = null): void {}
    };
}

describe('Client sessionFs option', function () {
    it('sends sessionFs.setProvider exactly once with the configured parameters', function () {
        $handlers = [];
        $requests = [];

        $client = startSessionFsClient(
            ['sessionFs' => sessionFsOptions(['capabilities' => ['sqlite' => true, 'binary' => false]])],
            $handlers,
            $requests,
        );

        expect($client->getState())->toBe(ConnectionState::CONNECTED)
            ->and($requests['sessionFs.setProvider'])->toBe([[
                'initialCwd' => '/workspace',
                'sessionStatePath' => '/state',
                'conventions' => 'posix',
                'capabilities' => ['sqlite' => true, 'binary' => false],
            ]]);

        // start() is idempotent, so it must not register the provider a second time.
        $client->start();
        expect($requests['sessionFs.setProvider'])->toHaveCount(1);
    });

    it('accepts the session_fs alias and SessionFsConfig instances', function () {
        $handlers = [];
        $requests = [];
        startSessionFsClient(['session_fs' => sessionFsOptions(['conventions' => 'windows'])], $handlers, $requests);

        expect($requests['sessionFs.setProvider'][0])->toBe([
            'initialCwd' => '/workspace',
            'sessionStatePath' => '/state',
            'conventions' => 'windows',
        ]);

        $handlers = [];
        $requests = [];
        startSessionFsClient(
            ['sessionFs' => new SessionFsConfig('/cwd', '/s', 'posix', new SessionFsSetProviderCapabilities(binary: true))],
            $handlers,
            $requests,
        );

        expect($requests['sessionFs.setProvider'][0])->toBe([
            'initialCwd' => '/cwd',
            'sessionStatePath' => '/s',
            'conventions' => 'posix',
            'capabilities' => ['binary' => true],
        ]);
    });

    it('omits empty capabilities from the request', function () {
        $handlers = [];
        $requests = [];
        startSessionFsClient(['sessionFs' => sessionFsOptions(['capabilities' => []])], $handlers, $requests);

        expect($requests['sessionFs.setProvider'][0])->not->toHaveKey('capabilities');
    });

    it('does not send sessionFs.setProvider without the option', function () {
        $handlers = [];
        $requests = [];
        startSessionFsClient([], $handlers, $requests);

        expect($requests)->not->toHaveKey('sessionFs.setProvider');
    });

    it('registers handlers for every sessionFs method', function () {
        $handlers = [];
        $requests = [];
        startSessionFsClient([], $handlers, $requests);

        foreach (['readFile', 'readFileBytes', 'writeFile', 'writeFileBytes', 'appendFile', 'exists', 'stat', 'mkdir', 'readdir', 'readdirWithTypes', 'rm', 'rename', 'sqliteQuery', 'sqliteTransaction', 'sqliteExists'] as $method) {
            expect($handlers)->toHaveKey("sessionFs.{$method}");
        }
    });

    it('rejects invalid sessionFs configuration', function (array|string $config, string $message) {
        expect(fn () => new Client(['sessionFs' => $config]))->toThrow(InvalidArgumentException::class, $message);
    })->with([
        'missing initialCwd' => [['sessionStatePath' => '/s', 'conventions' => 'posix'], 'sessionFs.initialCwd is required'],
        'empty initialCwd' => [sessionFsOptions(['initialCwd' => '']), 'sessionFs.initialCwd is required'],
        'missing sessionStatePath' => [['initialCwd' => '/w', 'conventions' => 'posix'], 'sessionFs.sessionStatePath is required'],
        'missing conventions' => [['initialCwd' => '/w', 'sessionStatePath' => '/s'], "sessionFs.conventions must be either 'windows' or 'posix'"],
        'invalid conventions' => [sessionFsOptions(['conventions' => 'unix']), "sessionFs.conventions must be either 'windows' or 'posix'"],
        'not an array' => ['/workspace', 'sessionFs must be a SessionFsConfig instance or an array'],
    ]);

    it('fails startup and stops the connection when setProvider fails', function () {
        $handlers = [];
        $requests = [];
        $client = null;

        $call = function () use (&$client, &$handlers, &$requests) {
            return startSessionFsClient(
                [],
                $handlers,
                $requests,
                ['sessionFs.setProvider' => new JsonRpcException(-32000, 'provider rejected')],
                function () use (&$client) {
                    $client = new Client(['sessionFs' => sessionFsOptions()]);

                    return $client->start();
                },
            );
        };

        expect($call)->toThrow(JsonRpcException::class, 'provider rejected');

        expect($client->getState())->toBe(ConnectionState::ERROR)
            ->and($requests['sessionFs.setProvider'])->toHaveCount(1)
            ->and($requests['__rpc.stop'])->toBe(1)
            ->and($requests['__process.stop'])->toBe(1);
    });

    it('registers the provider through CopilotManager config', function () {
        $handlers = [];
        $requests = [];
        $manager = new CopilotManager(['session_fs' => sessionFsOptions(['capabilities' => ['sqlite' => true]])]);

        startSessionFsClient([], $handlers, $requests, start: fn () => $manager->client());

        expect($requests['sessionFs.setProvider'])->toBe([[
            'initialCwd' => '/workspace',
            'sessionStatePath' => '/state',
            'conventions' => 'posix',
            'capabilities' => ['sqlite' => true],
        ]]);

        $manager->stop();
    });

    it('passes session_fs from the manager config and useStdio to the client options', function () {
        $captured = [];
        $this->app->bind(Client::class, function ($app, array $parameters) use (&$captured) {
            $captured[] = $parameters['options'];

            return Mockery::mock(CopilotClient::class, function ($mock) {
                $mock->shouldReceive('start');
                $mock->shouldReceive('stop')->andReturn([]);
            });
        });

        $manager = new CopilotManager(['session_fs' => sessionFsOptions()]);
        $manager->client();
        $manager->useStdio(['session_fs' => sessionFsOptions(['conventions' => 'windows']), 'unknown' => true]);
        $manager->useStdio(['sessionFs' => sessionFsOptions(['initialCwd' => '/other'])]);
        $manager->stop();

        expect($captured[0]['session_fs'])->toBe(sessionFsOptions())
            ->and($captured[1])->toBe(['session_fs' => sessionFsOptions(['conventions' => 'windows'])])
            ->and($captured[2])->toBe(['sessionFs' => sessionFsOptions(['initialCwd' => '/other'])]);
    });

    it('passes session_fs to the client for TCP URLs in the manager config', function () {
        $captured = [];
        $this->app->bind(Client::class, function ($app, array $parameters) use (&$captured) {
            $captured[] = $parameters['options'];

            return Mockery::mock(CopilotClient::class, function ($mock) {
                $mock->shouldReceive('start');
                $mock->shouldReceive('stop')->andReturn([]);
            });
        });

        $manager = new CopilotManager(['url' => '127.0.0.1:1234', 'session_fs' => sessionFsOptions()]);
        $manager->client();
        $manager->stop();

        expect($captured[0])->toBe([
            'cli_url' => '127.0.0.1:1234',
            'mode' => 'copilot-cli',
            'session_fs' => sessionFsOptions(),
        ]);
    });
});

describe('createSessionFsProvider', function () {
    it('requires the factory for create and resume when sessionFs is enabled', function () {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient(['sessionFs' => sessionFsOptions()], $handlers, $requests);

        expect(fn () => $client->createSession(['onPermissionRequest' => PermissionHandler::approveAll()]))
            ->toThrow(InvalidArgumentException::class, 'createSessionFsProvider is required in session config when sessionFs is enabled in client options.')
            ->and(fn () => $client->resumeSession('session-1', ['onPermissionRequest' => PermissionHandler::approveAll()]))
            ->toThrow(InvalidArgumentException::class, 'createSessionFsProvider is required')
            ->and($requests)->not->toHaveKeys(['session.create', 'session.resume']);
    });

    it('invokes the factory for create and resume and routes callbacks by session id', function () {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient(['sessionFs' => sessionFsOptions()], $handlers, $requests);
        $received = [];

        $factory = function (CopilotSession $session) use (&$received) {
            $received[] = $session->id();

            return sessionFsTestFactoryProvider();
        };

        $created = $client->createSession(new SessionConfig(
            sessionId: 'created-1',
            onPermissionRequest: PermissionHandler::approveAll(),
            createSessionFsProvider: $factory,
        ));
        $resumed = $client->resumeSession('resumed-1', new ResumeSessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            createSessionFsProvider: $factory,
        ));

        expect($received)->toBe(['created-1', 'resumed-1'])
            ->and($created)->toBeInstanceOf(Session::class)
            ->and($requests['session.create'][0]['hasSessionFsProvider'])->toBeTrue()
            ->and($requests['session.resume'][0]['hasSessionFsProvider'])->toBeTrue()
            ->and($handlers['sessionFs.readFile'](['sessionId' => 'created-1', 'path' => '/a']))->toBe(['content' => 'read:/a'])
            ->and($handlers['sessionFs.readFile'](['sessionId' => 'resumed-1', 'path' => '/b']))->toBe(['content' => 'read:/b']);

        // Callbacks for unknown sessions fail as JSON-RPC errors.
        expect(fn () => $handlers['sessionFs.readFile'](['sessionId' => 'other', 'path' => '/c']))
            ->toThrow(RuntimeException::class, 'Session not found');
    });

    it('accepts the factory in array configs including snake_case keys through fromArray', function () {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient(['sessionFs' => sessionFsOptions()], $handlers, $requests);
        $calls = 0;

        $client->createSession([
            'sessionId' => 'array-1',
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'createSessionFsProvider' => function () use (&$calls) {
                $calls++;

                return sessionFsTestFactoryProvider();
            },
        ]);
        $client->createSession(SessionConfig::fromArray([
            'sessionId' => 'array-2',
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'create_session_fs_provider' => function () use (&$calls) {
                $calls++;

                return ['readFile' => fn () => 'array', 'writeFile' => fn () => null];
            },
        ]));

        expect($calls)->toBe(2)
            ->and($handlers['sessionFs.readFile'](['sessionId' => 'array-2', 'path' => '/a']))->toBe(['content' => 'array']);
    });

    it('rejects providers that do not satisfy declared capabilities', function (array $capabilities, string $message) {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient(['sessionFs' => sessionFsOptions(['capabilities' => $capabilities])], $handlers, $requests);
        $config = [
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'createSessionFsProvider' => fn () => sessionFsTestFactoryProvider(),
        ];

        expect(fn () => $client->createSession($config))->toThrow(InvalidArgumentException::class, $message)
            ->and(fn () => $client->resumeSession('session-1', $config))->toThrow(InvalidArgumentException::class, $message)
            ->and($requests)->not->toHaveKeys(['session.create', 'session.resume'])
            ->and((new ReflectionProperty(Client::class, 'sessions'))->getValue($client))->toBe([]);
    })->with([
        'sqlite' => [['sqlite' => true], 'SessionFsConfig declares capabilities.sqlite but the provider does not implement sqlite.'],
        'binary' => [['binary' => true], 'SessionFsConfig declares capabilities.binary but the provider does not implement readFileBytes and writeFileBytes.'],
    ]);

    it('accepts providers that implement the declared capabilities', function () {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient(
            ['sessionFs' => sessionFsOptions(['capabilities' => ['sqlite' => true, 'binary' => true]])],
            $handlers,
            $requests,
        );

        $session = $client->createSession([
            'sessionId' => 'full-1',
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'createSessionFsProvider' => fn () => [
                'readFile' => fn () => '',
                'writeFile' => fn () => null,
                'readFileBytes' => fn () => '',
                'writeFileBytes' => fn () => null,
                'sqlite' => ['query' => fn () => null, 'exists' => fn () => true],
            ],
        ]);

        expect($session->id())->toBe('full-1')
            ->and($handlers['sessionFs.sqliteExists'](['sessionId' => 'full-1']))->toBe(['exists' => true]);

        $binary = new class implements SessionFsBinaryProvider
        {
            public function readFile(string $path): string
            {
                return '';
            }

            public function writeFile(string $path, string $content, ?int $mode = null): void {}

            public function readFileBytes(string $path): string
            {
                return '';
            }

            public function writeFileBytes(string $path, string $content, ?int $mode = null): void {}
        };
        $client = startSessionFsClient(['sessionFs' => sessionFsOptions(['capabilities' => ['binary' => true]])], $handlers, $requests);

        expect($client->resumeSession('bin-1', [
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'createSessionFsProvider' => fn () => $binary,
        ]))->toBeInstanceOf(Session::class);
    });

    it('rejects a factory that returns an invalid provider', function () {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient(['sessionFs' => sessionFsOptions()], $handlers, $requests);

        expect(fn () => $client->createSession([
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'createSessionFsProvider' => fn () => null,
        ]))->toThrow(InvalidArgumentException::class, 'must return a SessionFsProvider')
            ->and(fn () => $client->createSession([
                'onPermissionRequest' => PermissionHandler::approveAll(),
                'createSessionFsProvider' => fn () => ['readFile' => fn () => ''],
            ]))->toThrow(InvalidArgumentException::class, 'sessionFsProvider must implement SessionFsProvider');
    });

    it('does not require or invoke the factory when the client has no sessionFs option', function () {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient([], $handlers, $requests);
        $invoked = false;

        $client->createSession([
            'sessionId' => 'plain-1',
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'createSessionFsProvider' => function () use (&$invoked) {
                $invoked = true;

                return sessionFsTestFactoryProvider();
            },
        ]);
        $client->createSession(['sessionId' => 'plain-2', 'onPermissionRequest' => PermissionHandler::approveAll()]);

        expect($invoked)->toBeFalse()
            ->and($requests['session.create'][0])->not->toHaveKey('hasSessionFsProvider');
    });

    it('keeps the legacy sessionFsProvider option working without the client option', function () {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient([], $handlers, $requests);

        $client->createSession([
            'sessionId' => 'legacy-1',
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'sessionFsProvider' => sessionFsTestFactoryProvider(),
        ]);

        expect($requests)->not->toHaveKey('sessionFs.setProvider')
            ->and($requests['session.create'][0]['hasSessionFsProvider'])->toBeTrue()
            ->and($handlers['sessionFs.readFile'](['sessionId' => 'legacy-1', 'path' => '/a']))->toBe(['content' => 'read:/a'])
            ->and($handlers['sessionFs.appendFile'](['sessionId' => 'legacy-1', 'path' => '/a', 'content' => 'x']))
            ->toBe(['code' => 'UNKNOWN', 'message' => 'sessionFs.appendFile is not supported by this provider']);
    });

    it('dispatches every extended callback through the client handlers', function () {
        $handlers = [];
        $requests = [];
        $client = startSessionFsClient(['sessionFs' => sessionFsOptions()], $handlers, $requests);
        $client->createSession([
            'sessionId' => 's-1',
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'createSessionFsProvider' => fn () => [
                'readFile' => fn () => '',
                'writeFile' => fn () => null,
                'appendFile' => fn () => null,
                'exists' => fn () => true,
                'stat' => fn () => ['birthtime' => 'b', 'mtime' => 'm', 'isFile' => false, 'isDirectory' => true, 'size' => 0],
                'mkdir' => fn () => null,
                'readdir' => fn () => ['a'],
                'readdirWithTypes' => fn () => [['name' => 'a', 'type' => 'file']],
                'rm' => fn () => null,
                'rename' => fn () => null,
                'sqlite' => [
                    'query' => fn () => ['columns' => ['c'], 'rows' => [], 'rowsAffected' => 0],
                    'exists' => fn () => false,
                    'transaction' => fn (array $statements) => [['columns' => [], 'rows' => [], 'rowsAffected' => count($statements)]],
                ],
            ],
        ]);
        $base = ['sessionId' => 's-1', 'path' => '/p'];

        expect($handlers['sessionFs.appendFile']($base + ['content' => 'x']))->toBe([])
            ->and($handlers['sessionFs.exists']($base))->toBe(['exists' => true])
            ->and($handlers['sessionFs.stat']($base)['isDirectory'])->toBeTrue()
            ->and($handlers['sessionFs.mkdir']($base + ['recursive' => true]))->toBe([])
            ->and($handlers['sessionFs.readdir']($base))->toBe(['entries' => ['a']])
            ->and($handlers['sessionFs.readdirWithTypes']($base))->toBe(['entries' => [['name' => 'a', 'type' => 'file']]])
            ->and($handlers['sessionFs.rm']($base + ['force' => true]))->toBe([])
            ->and($handlers['sessionFs.rename'](['sessionId' => 's-1', 'src' => '/a', 'dest' => '/b']))->toBe([])
            ->and($handlers['sessionFs.sqliteQuery'](['sessionId' => 's-1', 'query' => 'select', 'queryType' => 'query'])['columns'])->toBe(['c'])
            ->and($handlers['sessionFs.sqliteExists'](['sessionId' => 's-1']))->toBe(['exists' => false])
            ->and($handlers['sessionFs.sqliteTransaction'](['sessionId' => 's-1', 'statements' => [['query' => 'x', 'queryType' => 'run']]])['results'][0]['rowsAffected'])->toBe(1);
    });
});
