<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Events\Session\CreateSession;
use Revolution\Copilot\Events\Session\MessageSend;
use Revolution\Copilot\Events\Session\MessageSendAndWait;
use Revolution\Copilot\Events\Session\ResumeSession;
use Revolution\Copilot\Events\Session\SessionEventReceived;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\SessionEvent;

describe('MessageSend', function () {
    it('stores all required properties', function () {
        $event = new MessageSend(
            sessionId: 'session-1',
            messageId: 'msg-1',
            prompt: 'Hello world',
        );

        expect($event->sessionId)->toBe('session-1')
            ->and($event->messageId)->toBe('msg-1')
            ->and($event->prompt)->toBe('Hello world')
            ->and($event->attachments)->toBeNull()
            ->and($event->mode)->toBeNull();
    });

    it('stores optional attachments and mode', function () {
        $attachments = [['type' => 'file', 'path' => '/tmp/file.txt']];
        $event = new MessageSend(
            sessionId: 'session-1',
            messageId: 'msg-2',
            prompt: 'Review this file',
            attachments: $attachments,
            mode: 'plan',
        );

        expect($event->attachments)->toBe($attachments)
            ->and($event->mode)->toBe('plan');
    });
});

describe('MessageSendAndWait', function () {
    it('stores properties with null lastAssistantMessage', function () {
        $event = new MessageSendAndWait(
            sessionId: 'session-1',
            lastAssistantMessage: null,
            prompt: 'What is 2 + 2?',
        );

        expect($event->sessionId)->toBe('session-1')
            ->and($event->lastAssistantMessage)->toBeNull()
            ->and($event->prompt)->toBe('What is 2 + 2?')
            ->and($event->attachments)->toBeNull()
            ->and($event->mode)->toBeNull();
    });

    it('stores session event as lastAssistantMessage', function () {
        $sessionEvent = SessionEvent::fromArray([
            'id' => 'evt-1',
            'timestamp' => '2024-01-01T00:00:00Z',
            'parentId' => null,
            'type' => SessionEventType::ASSISTANT_MESSAGE->value,
            'data' => ['content' => 'The answer is 4'],
        ]);

        $event = new MessageSendAndWait(
            sessionId: 'session-1',
            lastAssistantMessage: $sessionEvent,
            prompt: 'Continue',
            mode: 'interactive',
        );

        expect($event->lastAssistantMessage)->toBe($sessionEvent)
            ->and($event->mode)->toBe('interactive');
    });
});

describe('SessionEventReceived', function () {
    it('stores session ID and event', function () {
        $sessionEvent = SessionEvent::fromArray([
            'id' => 'evt-2',
            'timestamp' => '2024-01-01T00:00:00Z',
            'parentId' => null,
            'type' => SessionEventType::SESSION_INFO->value,
            'data' => [],
        ]);

        $event = new SessionEventReceived('session-1', $sessionEvent);

        expect($event->sessionId)->toBe('session-1')
            ->and($event->event)->toBe($sessionEvent);
    });
});

describe('CreateSession', function () {
    it('stores session reference', function () {
        $session = Mockery::mock(Session::class);
        $event = new CreateSession($session);

        expect($event->session)->toBe($session);
    });
});

describe('ResumeSession', function () {
    it('stores session reference', function () {
        $session = Mockery::mock(Session::class);
        $event = new ResumeSession($session);

        expect($event->session)->toBe($session);
    });
});
