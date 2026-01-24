<?php

declare(strict_types=1);

use Revolution\Copilot\Types\SessionMetadata;

describe('SessionMetadata', function () {
    it('can be created from array with all fields', function () {
        $metadata = SessionMetadata::fromArray([
            'sessionId' => 'session-123',
            'startTime' => '2026-01-24T10:00:00Z',
            'modifiedTime' => '2026-01-24T10:30:00Z',
            'summary' => 'Test session summary',
            'isRemote' => true,
        ]);

        expect($metadata->sessionId)->toBe('session-123')
            ->and($metadata->startTime)->toBe('2026-01-24T10:00:00Z')
            ->and($metadata->modifiedTime)->toBe('2026-01-24T10:30:00Z')
            ->and($metadata->summary)->toBe('Test session summary')
            ->and($metadata->isRemote)->toBeTrue();
    });

    it('can be created from array with minimal fields', function () {
        $metadata = SessionMetadata::fromArray([
            'sessionId' => 'session-456',
            'startTime' => '2026-01-24T10:00:00Z',
            'modifiedTime' => '2026-01-24T10:00:00Z',
        ]);

        expect($metadata->sessionId)->toBe('session-456')
            ->and($metadata->startTime)->toBe('2026-01-24T10:00:00Z')
            ->and($metadata->modifiedTime)->toBe('2026-01-24T10:00:00Z')
            ->and($metadata->summary)->toBeNull()
            ->and($metadata->isRemote)->toBeFalse();
    });

    it('can convert to array with all fields', function () {
        $metadata = new SessionMetadata(
            sessionId: 'session-789',
            startTime: '2026-01-24T11:00:00Z',
            modifiedTime: '2026-01-24T11:30:00Z',
            summary: 'Another summary',
            isRemote: true,
        );

        $array = $metadata->toArray();

        expect($array['sessionId'])->toBe('session-789')
            ->and($array['startTime'])->toBe('2026-01-24T11:00:00Z')
            ->and($array['modifiedTime'])->toBe('2026-01-24T11:30:00Z')
            ->and($array['summary'])->toBe('Another summary')
            ->and($array['isRemote'])->toBeTrue();
    });

    it('filters null values in toArray', function () {
        $metadata = new SessionMetadata(
            sessionId: 'session-abc',
            startTime: '2026-01-24T12:00:00Z',
            modifiedTime: '2026-01-24T12:00:00Z',
        );

        $array = $metadata->toArray();

        expect($array)->toBe([
            'sessionId' => 'session-abc',
            'startTime' => '2026-01-24T12:00:00Z',
            'modifiedTime' => '2026-01-24T12:00:00Z',
            'isRemote' => false,
        ]);
        expect($array)->not->toHaveKey('summary');
    });

    it('implements Arrayable interface', function () {
        $metadata = new SessionMetadata(
            sessionId: 'session-xyz',
            startTime: '2026-01-24T13:00:00Z',
            modifiedTime: '2026-01-24T13:00:00Z',
        );

        expect($metadata)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
