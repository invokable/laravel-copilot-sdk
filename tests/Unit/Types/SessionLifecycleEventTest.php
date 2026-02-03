<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionLifecycleEventType;
use Revolution\Copilot\Types\SessionLifecycleEvent;
use Revolution\Copilot\Types\SessionLifecycleEventMetadata;

describe('SessionLifecycleEventMetadata', function () {
    it('can be created from array with all fields', function () {
        $metadata = SessionLifecycleEventMetadata::fromArray([
            'startTime' => '2026-01-24T10:00:00Z',
            'modifiedTime' => '2026-01-24T10:30:00Z',
            'summary' => 'Test session summary',
        ]);

        expect($metadata->startTime)->toBe('2026-01-24T10:00:00Z')
            ->and($metadata->modifiedTime)->toBe('2026-01-24T10:30:00Z')
            ->and($metadata->summary)->toBe('Test session summary');
    });

    it('can be created from array without summary', function () {
        $metadata = SessionLifecycleEventMetadata::fromArray([
            'startTime' => '2026-01-24T10:00:00Z',
            'modifiedTime' => '2026-01-24T10:30:00Z',
        ]);

        expect($metadata->startTime)->toBe('2026-01-24T10:00:00Z')
            ->and($metadata->modifiedTime)->toBe('2026-01-24T10:30:00Z')
            ->and($metadata->summary)->toBeNull();
    });

    it('can convert to array', function () {
        $metadata = new SessionLifecycleEventMetadata(
            startTime: '2026-01-24T10:00:00Z',
            modifiedTime: '2026-01-24T10:30:00Z',
            summary: 'Test summary',
        );

        $array = $metadata->toArray();

        expect($array)->toBe([
            'startTime' => '2026-01-24T10:00:00Z',
            'modifiedTime' => '2026-01-24T10:30:00Z',
            'summary' => 'Test summary',
        ]);
    });

    it('filters null values in toArray', function () {
        $metadata = new SessionLifecycleEventMetadata(
            startTime: '2026-01-24T10:00:00Z',
            modifiedTime: '2026-01-24T10:30:00Z',
        );

        $array = $metadata->toArray();

        expect($array)->toBe([
            'startTime' => '2026-01-24T10:00:00Z',
            'modifiedTime' => '2026-01-24T10:30:00Z',
        ]);
    });
});

describe('SessionLifecycleEvent', function () {
    it('can be created from array with all fields', function () {
        $event = SessionLifecycleEvent::fromArray([
            'type' => 'session.created',
            'sessionId' => 'session-123',
            'metadata' => [
                'startTime' => '2026-01-24T10:00:00Z',
                'modifiedTime' => '2026-01-24T10:30:00Z',
                'summary' => 'Test summary',
            ],
        ]);

        expect($event->type)->toBe(SessionLifecycleEventType::SESSION_CREATED)
            ->and($event->sessionId)->toBe('session-123')
            ->and($event->metadata)->toBeInstanceOf(SessionLifecycleEventMetadata::class)
            ->and($event->metadata->summary)->toBe('Test summary');
    });

    it('can be created from array without metadata', function () {
        $event = SessionLifecycleEvent::fromArray([
            'type' => 'session.deleted',
            'sessionId' => 'session-456',
        ]);

        expect($event->type)->toBe(SessionLifecycleEventType::SESSION_DELETED)
            ->and($event->sessionId)->toBe('session-456')
            ->and($event->metadata)->toBeNull();
    });

    it('can be created for foreground event', function () {
        $event = SessionLifecycleEvent::fromArray([
            'type' => 'session.foreground',
            'sessionId' => 'session-789',
        ]);

        expect($event->type)->toBe(SessionLifecycleEventType::SESSION_FOREGROUND)
            ->and($event->sessionId)->toBe('session-789');
    });

    it('can be created for background event', function () {
        $event = SessionLifecycleEvent::fromArray([
            'type' => 'session.background',
            'sessionId' => 'session-abc',
        ]);

        expect($event->type)->toBe(SessionLifecycleEventType::SESSION_BACKGROUND)
            ->and($event->sessionId)->toBe('session-abc');
    });

    it('can convert to array with all fields', function () {
        $event = new SessionLifecycleEvent(
            type: SessionLifecycleEventType::SESSION_UPDATED,
            sessionId: 'session-xyz',
            metadata: new SessionLifecycleEventMetadata(
                startTime: '2026-01-24T10:00:00Z',
                modifiedTime: '2026-01-24T11:00:00Z',
            ),
        );

        $array = $event->toArray();

        expect($array['type'])->toBe('session.updated')
            ->and($array['sessionId'])->toBe('session-xyz')
            ->and($array['metadata']['startTime'])->toBe('2026-01-24T10:00:00Z');
    });

    it('filters null values in toArray', function () {
        $event = new SessionLifecycleEvent(
            type: SessionLifecycleEventType::SESSION_DELETED,
            sessionId: 'session-del',
        );

        $array = $event->toArray();

        expect($array)->toBe([
            'type' => 'session.deleted',
            'sessionId' => 'session-del',
        ]);
    });

    it('implements Arrayable interface', function () {
        $event = new SessionLifecycleEvent(
            type: SessionLifecycleEventType::SESSION_CREATED,
            sessionId: 'session-new',
        );

        expect($event)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
