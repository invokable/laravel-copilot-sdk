<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\SessionEvent;

describe('SessionEvent', function () {
    it('can be created from array', function () {
        $event = SessionEvent::fromArray([
            'id' => 'test-id',
            'timestamp' => '2024-01-01T00:00:00Z',
            'parentId' => 'parent-id',
            'type' => 'assistant.message',
            'data' => ['content' => 'Hello World'],
            'ephemeral' => false,
        ]);

        expect($event->id)->toBe('test-id')
            ->and($event->timestamp)->toBe('2024-01-01T00:00:00Z')
            ->and($event->parentId)->toBe('parent-id')
            ->and($event->type)->toBe(SessionEventType::ASSISTANT_MESSAGE)
            ->and($event->data)->toBe(['content' => 'Hello World'])
            ->and($event->ephemeral)->toBeFalse();
    });

    it('handles missing fields with defaults', function () {
        $event = SessionEvent::fromArray([]);

        expect($event->id)->toBe('')
            ->and($event->timestamp)->toBe('')
            ->and($event->parentId)->toBeNull()
            ->and($event->type)->toBe(SessionEventType::SESSION_INFO)
            ->and($event->data)->toBe([])
            ->and($event->ephemeral)->toBeFalse();
    });

    it('can check if is assistant message', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.message',
            'data' => ['content' => 'Test'],
        ]);

        expect($event->isAssistantMessage())->toBeTrue()
            ->and($event->isIdle())->toBeFalse()
            ->and($event->failed())->toBeFalse();
    });

    it('can check if is idle', function () {
        $event = SessionEvent::fromArray([
            'type' => 'session.idle',
        ]);

        expect($event->isIdle())->toBeTrue()
            ->and($event->isAssistantMessage())->toBeFalse()
            ->and($event->failed())->toBeFalse();
    });

    it('can check if is error', function () {
        $event = SessionEvent::fromArray([
            'type' => 'session.error',
            'data' => ['message' => 'Something went wrong'],
        ]);

        expect($event->failed())->toBeTrue()
            ->and($event->isAssistantMessage())->toBeFalse()
            ->and($event->isIdle())->toBeFalse();
    });

    it('can get content from assistant message', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.message',
            'data' => ['content' => 'Hello World'],
        ]);

        expect($event->content())->toBe('Hello World');
    });

    it('returns null for content when not present', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.message',
            'data' => [],
        ]);

        expect($event->content())->toBeNull();
    });

    it('can get error message', function () {
        $event = SessionEvent::fromArray([
            'type' => 'session.error',
            'data' => ['message' => 'Error occurred'],
        ]);

        expect($event->errorMessage())->toBe('Error occurred');
    });

    it('can convert to array', function () {
        $event = SessionEvent::fromArray([
            'id' => 'test-id',
            'timestamp' => '2024-01-01T00:00:00Z',
            'parentId' => null,
            'type' => 'assistant.message',
            'data' => ['content' => 'Test'],
            'ephemeral' => true,
        ]);

        $array = $event->toArray();

        expect($array)->toBe([
            'id' => 'test-id',
            'timestamp' => '2024-01-01T00:00:00Z',
            'parentId' => null,
            'type' => 'assistant.message',
            'data' => ['content' => 'Test'],
            'ephemeral' => true,
        ]);
    });
});

describe('SessionEventType', function () {
    it('has all expected event types', function () {
        expect(SessionEventType::SESSION_START->value)->toBe('session.start')
            ->and(SessionEventType::SESSION_IDLE->value)->toBe('session.idle')
            ->and(SessionEventType::SESSION_ERROR->value)->toBe('session.error')
            ->and(SessionEventType::ASSISTANT_MESSAGE->value)->toBe('assistant.message')
            ->and(SessionEventType::USER_MESSAGE->value)->toBe('user.message')
            ->and(SessionEventType::TOOL_EXECUTION_START->value)->toBe('tool.execution_start')
            ->and(SessionEventType::TOOL_EXECUTION_COMPLETE->value)->toBe('tool.execution_complete');
    });

    it('can be created from string value', function () {
        $type = SessionEventType::tryFrom('assistant.message');

        expect($type)->toBe(SessionEventType::ASSISTANT_MESSAGE);
    });

    it('returns null for invalid value', function () {
        $type = SessionEventType::tryFrom('invalid.type');

        expect($type)->toBeNull();
    });

    it('has new event types from latest session-events schema', function () {
        expect(SessionEventType::SESSION_TITLE_CHANGED->value)->toBe('session.title_changed')
            ->and(SessionEventType::SESSION_WARNING->value)->toBe('session.warning')
            ->and(SessionEventType::SESSION_MODE_CHANGED->value)->toBe('session.mode_changed')
            ->and(SessionEventType::SESSION_PLAN_CHANGED->value)->toBe('session.plan_changed')
            ->and(SessionEventType::SESSION_WORKSPACE_FILE_CHANGED->value)->toBe('session.workspace_file_changed')
            ->and(SessionEventType::SESSION_TASK_COMPLETE->value)->toBe('session.task_complete')
            ->and(SessionEventType::ASSISTANT_STREAMING_DELTA->value)->toBe('assistant.streaming_delta');
    });
});
