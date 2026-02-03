<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionLifecycleEventType;

describe('SessionLifecycleEventType', function () {
    it('has session.created case', function () {
        expect(SessionLifecycleEventType::SESSION_CREATED->value)->toBe('session.created');
    });

    it('has session.deleted case', function () {
        expect(SessionLifecycleEventType::SESSION_DELETED->value)->toBe('session.deleted');
    });

    it('has session.updated case', function () {
        expect(SessionLifecycleEventType::SESSION_UPDATED->value)->toBe('session.updated');
    });

    it('has session.foreground case', function () {
        expect(SessionLifecycleEventType::SESSION_FOREGROUND->value)->toBe('session.foreground');
    });

    it('has session.background case', function () {
        expect(SessionLifecycleEventType::SESSION_BACKGROUND->value)->toBe('session.background');
    });

    it('can be created from value', function () {
        expect(SessionLifecycleEventType::from('session.created'))
            ->toBe(SessionLifecycleEventType::SESSION_CREATED);
    });

    it('can be created from all valid values', function () {
        $values = ['session.created', 'session.deleted', 'session.updated', 'session.foreground', 'session.background'];

        foreach ($values as $value) {
            expect(SessionLifecycleEventType::from($value))->toBeInstanceOf(SessionLifecycleEventType::class);
        }
    });
});
