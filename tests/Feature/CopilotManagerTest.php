<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\CopilotManager;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\SessionEvent;

beforeEach(function () {
    Copilot::clearResolvedInstances();
});

describe('CopilotManager', function () {
    it('can be instantiated with config', function () {
        $manager = new CopilotManager([
            'cli_path' => '/usr/bin/copilot',
            'timeout' => 120,
        ]);

        expect($manager)->toBeInstanceOf(CopilotManager::class);
    });

    it('returns null client before client is called', function () {
        $manager = new CopilotManager;

        // Use reflection to check internal state
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);

        expect($property->getValue($manager))->toBeNull();
    });

    it('creates client via app container', function () {
        // Mock the Client class
        $mockClient = Mockery::mock(CopilotClient::class);
        $mockClient->shouldReceive('start')->once();
        $mockClient->shouldReceive('stop')->andReturn([]); // For destructor

        $this->app->bind(Client::class, fn () => $mockClient);

        $manager = new CopilotManager(['cli_path' => '/test/path']);
        $client = $manager->client();

        expect($client)->toBe($mockClient);

        // Prevent destructor from calling stop twice
        $manager->stop();
    });

    it('reuses existing client on subsequent client calls', function () {
        $mockClient = Mockery::mock(CopilotClient::class);
        $mockClient->shouldReceive('start')->once(); // Should only be called once
        $mockClient->shouldReceive('stop')->andReturn([]); // For destructor

        $this->app->bind(Client::class, fn () => $mockClient);

        $manager = new CopilotManager;

        $client1 = $manager->client();
        $client2 = $manager->client();

        expect($client1)->toBe($client2);

        // Prevent destructor from calling stop twice
        $manager->stop();
    });

    it('stop method clears the client', function () {
        $mockClient = Mockery::mock(CopilotClient::class);
        $mockClient->shouldReceive('start')->once();
        $mockClient->shouldReceive('stop')->once();

        $this->app->bind(Client::class, fn () => $mockClient);

        $manager = new CopilotManager;
        $manager->client();
        $manager->stop();

        // Use reflection to verify client is null
        $reflection = new ReflectionClass($manager);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);

        expect($property->getValue($manager))->toBeNull();
    });

    it('start method calls callback with session', function () {
        $mockSession = Mockery::mock(CopilotSession::class);
        $mockSession->shouldReceive('destroy')->once();

        $mockClient = Mockery::mock(CopilotClient::class);
        $mockClient->shouldReceive('start')->once();
        $mockClient->shouldReceive('createSession')->once()->andReturn($mockSession);
        $mockClient->shouldReceive('stop')->andReturn([]);

        $this->app->bind(Client::class, fn () => $mockClient);

        $manager = new CopilotManager;
        $callbackCalled = false;
        $receivedSession = null;

        $result = $manager->start(function (CopilotSession $session) use (&$callbackCalled, &$receivedSession) {
            $callbackCalled = true;
            $receivedSession = $session;

            return 'callback-result';
        });

        expect($callbackCalled)->toBeTrue()
            ->and($receivedSession)->toBe($mockSession)
            ->and($result)->toBe('callback-result');

        $manager->stop();
    });

    it('start method destroys session even on exception', function () {
        $mockSession = Mockery::mock(CopilotSession::class);
        $mockSession->shouldReceive('destroy')->once(); // Must be called even on exception

        $mockClient = Mockery::mock(CopilotClient::class);
        $mockClient->shouldReceive('start')->once();
        $mockClient->shouldReceive('createSession')->once()->andReturn($mockSession);
        $mockClient->shouldReceive('stop')->andReturn([]);

        $this->app->bind(Client::class, fn () => $mockClient);

        $manager = new CopilotManager;

        try {
            $manager->start(function () {
                throw new RuntimeException('Test error');
            });
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toBe('Test error');
        }

        $manager->stop();
    });

    it('createSession returns session from client', function () {
        $mockSession = Mockery::mock(CopilotSession::class);

        $mockClient = Mockery::mock(CopilotClient::class);
        $mockClient->shouldReceive('start')->once();
        $mockClient->shouldReceive('createSession')
            ->with(Mockery::on(fn ($config) => isset($config->toArray()['model'])))
            ->once()
            ->andReturn($mockSession);
        $mockClient->shouldReceive('stop')->andReturn([]);

        $this->app->bind(Client::class, fn () => $mockClient);

        $manager = new CopilotManager(['model' => 'gpt-4']);
        $session = $manager->createSession();

        expect($session)->toBe($mockSession);

        $manager->stop();
    });

    it('run method executes sendAndWait', function () {
        $mockEvent = SessionEvent::fromArray([
            'type' => 'assistant.message',
            'data' => ['content' => 'Test response'],
        ]);

        $mockSession = Mockery::mock(CopilotSession::class);
        $mockSession->shouldReceive('sendAndWait')
            ->with('test prompt', null, null, 60.0)
            ->once()
            ->andReturn($mockEvent);
        $mockSession->shouldReceive('destroy')->once();

        $mockClient = Mockery::mock(CopilotClient::class);
        $mockClient->shouldReceive('start')->once();
        $mockClient->shouldReceive('createSession')->once()->andReturn($mockSession);
        $mockClient->shouldReceive('stop')->andReturn([]);

        $this->app->bind(Client::class, fn () => $mockClient);

        $manager = new CopilotManager(['timeout' => 60.0]);
        $result = $manager->run('test prompt');

        expect($result)->toBe($mockEvent)
            ->and($result->content())->toBe('Test response');

        $manager->stop();
    });

    it('run uses configured timeout', function () {
        $mockSession = Mockery::mock(CopilotSession::class);
        $mockSession->shouldReceive('sendAndWait')
            ->with('prompt', null, null, 120.0) // Custom timeout
            ->once()
            ->andReturn(null);
        $mockSession->shouldReceive('destroy')->once();

        $mockClient = Mockery::mock(CopilotClient::class);
        $mockClient->shouldReceive('start')->once();
        $mockClient->shouldReceive('createSession')->once()->andReturn($mockSession);
        $mockClient->shouldReceive('stop')->andReturn([]);

        $this->app->bind(Client::class, fn () => $mockClient);

        $manager = new CopilotManager(['timeout' => 120.0]);
        $manager->run('prompt');

        $manager->stop();
    });
});
