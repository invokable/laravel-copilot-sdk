<?php

declare(strict_types=1);

use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\SessionEvent;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    // Don't use preventStrayRequests here as Session tests use mocked RPC client
});

describe('Session', function () {
    it('can be instantiated with sessionId and client', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);

        $session = new Session('test-session-id', $mockClient);

        expect($session->id())->toBe('test-session-id')
            ->and($session->sessionId)->toBe('test-session-id');
    });

    it('send dispatches session.send request', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.send', [
                'sessionId' => 'test-session',
                'prompt' => 'Hello World',
                'attachments' => null,
                'mode' => null,
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

    it('on registers event handler', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $handlerCalled = false;
        $receivedEvent = null;

        $unsubscribe = $session->on(function (SessionEvent $event) use (&$handlerCalled, &$receivedEvent) {
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

        $session->on(Revolution\Copilot\Enums\SessionEventType::ASSISTANT_MESSAGE, function (SessionEvent $event) use (&$messageCallCount, &$receivedEvent) {
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

    it('getMessages returns session events', function () {
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
        $messages = $session->getMessages();

        expect($messages)->toHaveCount(2)
            ->and($messages[0])->toBeInstanceOf(SessionEvent::class)
            ->and($messages[0]->type->value)->toBe('user.message')
            ->and($messages[1]->type())->toBe('assistant.message');
    });

    it('destroy sends destroy request and clears handlers', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $mockClient->shouldReceive('request')
            ->with('session.destroy', ['sessionId' => 'test-session'])
            ->once()
            ->andReturn([]);

        $session = new Session('test-session', $mockClient);

        // Register a handler
        $session->on(fn () => null);
        $session->registerTools([['name' => 'test', 'handler' => fn () => null]]);
        $session->registerPermissionHandler(fn () => ['kind' => 'approved']);

        $session->destroy();

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

        expect($result)->toBe(['kind' => 'denied-no-approval-rule-and-could-not-request-from-user']);
    });

    it('handlePermissionRequest returns denied on handler error', function () {
        $mockClient = Mockery::mock(JsonRpcClient::class);
        $session = new Session('test-session', $mockClient);

        $session->registerPermissionHandler(function () {
            throw new RuntimeException('Handler error');
        });

        $result = $session->handlePermissionRequest(['toolName' => 'bash']);

        expect($result)->toBe(['kind' => 'denied-no-approval-rule-and-could-not-request-from-user']);
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
        Revolt\EventLoop::delay(0.02, function () use ($session) {
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
            ->toThrow(Revolution\Copilot\Exceptions\SessionErrorException::class, 'Session error: Test error');
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
            ->toThrow(Revolution\Copilot\Exceptions\SessionTimeoutException::class);
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
