<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Events\Session\ResumeSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Process\ProcessManager;
use Revolution\Copilot\Session;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    // Don't use preventStrayRequests here as Client tests use mocked RPC client
});

describe('Client', function () {
    it('can be instantiated with options', function () {
        $client = new Client([
            'cli_path' => '/test/copilot',
            'log_level' => 'debug',
        ]);

        expect($client)->toBeInstanceOf(Client::class)
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED);
    });

    it('creates ProcessManager via app container', function () {
        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->never(); // Not started yet

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);

        $client = new Client(['cli_path' => '/test/copilot']);

        expect($client->getState())->toBe(ConnectionState::DISCONNECTED);
    });

    it('start method connects to server', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdin')->andReturn($stdin);
        $mockProcessManager->shouldReceive('getStdout')->andReturn($stdout);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('ping', Mockery::any(), Mockery::any())
            ->once()
            ->andReturn(['protocolVersion' => 2, 'message' => 'pong', 'timestamp' => time()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();

        expect($client->getState())->toBe(ConnectionState::CONNECTED);

        fclose($stdin);
        fclose($stdout);
    });

    it('start method is idempotent when already connected', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once(); // Only once
        $mockProcessManager->shouldReceive('getStdin')->andReturn($stdin);
        $mockProcessManager->shouldReceive('getStdout')->andReturn($stdout);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('ping', Mockery::any(), Mockery::any())
            ->once()
            ->andReturn(['protocolVersion' => 2, 'message' => 'pong', 'timestamp' => time()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();
        $client->start(); // Second call should be a no-op

        expect($client->getState())->toBe(ConnectionState::CONNECTED);

        fclose($stdin);
        fclose($stdout);
    });

    it('throws when protocol version mismatch', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdin')->andReturn($stdin);
        $mockProcessManager->shouldReceive('getStdout')->andReturn($stdout);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('ping', Mockery::any(), Mockery::any())
            ->once()
            ->andReturn(['protocolVersion' => 999, 'message' => 'pong', 'timestamp' => time()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;

        expect(fn () => $client->start())
            ->toThrow(RuntimeException::class, 'SDK protocol version mismatch');

        fclose($stdin);
        fclose($stdout);
    });

    it('stop method cleans up resources', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('stop')->once();
        $mockProcessManager->shouldReceive('getStdin')->andReturn($stdin);
        $mockProcessManager->shouldReceive('getStdout')->andReturn($stdout);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('stop')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('ping', Mockery::any(), Mockery::any())
            ->once()
            ->andReturn(['protocolVersion' => 2, 'message' => 'pong', 'timestamp' => time()]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();
        $errors = $client->stop();

        expect($errors)->toBe([])
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED);

        fclose($stdin);
        fclose($stdout);
    });

    it('createSession creates and returns session', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdin')->andReturn($stdin);
        $mockProcessManager->shouldReceive('getStdout')->andReturn($stdout);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('ping', Mockery::any(), Mockery::any())
            ->once()
            ->andReturn(['protocolVersion' => 2, 'message' => 'pong', 'timestamp' => time()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.create', Mockery::any())
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('registerTools')->once()->with([]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->createSession();

        expect($session)->toBe($mockSession);

        fclose($stdin);
        fclose($stdout);
    });

    it('throws when createSession called without connection', function () {
        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->never();

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);

        $client = new Client;

        expect(fn () => $client->createSession())
            ->toThrow(RuntimeException::class, 'Client not connected');
    });

    it('resumeSession resume and returns session', function () {
        Event::fake();

        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdin')->andReturn($stdin);
        $mockProcessManager->shouldReceive('getStdout')->andReturn($stdout);

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('ping', Mockery::any(), Mockery::any())
            ->once()
            ->andReturn(['protocolVersion' => 2, 'message' => 'pong', 'timestamp' => time()]);
        $mockRpcClient->shouldReceive('request')
            ->with('session.resume', Mockery::any())
            ->once()
            ->andReturn(['sessionId' => 'test-session-123']);

        $mockSession = Mockery::mock(Session::class);
        $mockSession->shouldReceive('registerTools')->once()->with([]);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);
        $this->app->bind(Session::class, fn () => $mockSession);

        $client = new Client;
        $client->start();
        $session = $client->resumeSession('test-session-123');

        expect($session)->toBe($mockSession);

        Event::assertDispatched(ResumeSession::class);

        fclose($stdin);
        fclose($stdout);
    });

    it('ping returns server response', function () {
        $stdin = fopen('php://memory', 'r+');
        $stdout = fopen('php://memory', 'r+');

        $mockProcessManager = Mockery::mock(ProcessManager::class);
        $mockProcessManager->shouldReceive('start')->once();
        $mockProcessManager->shouldReceive('getStdin')->andReturn($stdin);
        $mockProcessManager->shouldReceive('getStdout')->andReturn($stdout);

        $expectedResponse = ['message' => 'hello', 'timestamp' => 1234567890, 'protocolVersion' => 2];

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('ping', Mockery::any(), Mockery::any())
            ->andReturn($expectedResponse);

        $this->app->bind(ProcessManager::class, fn () => $mockProcessManager);
        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client;
        $client->start();

        $result = $client->ping('hello');

        expect($result)->toBe($expectedResponse);

        fclose($stdin);
        fclose($stdout);
    });
});
