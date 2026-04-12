<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ConnectionState;

describe('ConnectionState', function () {
    it('has correct string values', function () {
        expect(ConnectionState::DISCONNECTED->value)->toBe('disconnected')
            ->and(ConnectionState::CONNECTING->value)->toBe('connecting')
            ->and(ConnectionState::CONNECTED->value)->toBe('connected')
            ->and(ConnectionState::ERROR->value)->toBe('error');
    });

    it('can be created from string', function () {
        expect(ConnectionState::from('disconnected'))->toBe(ConnectionState::DISCONNECTED)
            ->and(ConnectionState::from('connecting'))->toBe(ConnectionState::CONNECTING)
            ->and(ConnectionState::from('connected'))->toBe(ConnectionState::CONNECTED)
            ->and(ConnectionState::from('error'))->toBe(ConnectionState::ERROR);
    });

    it('has all expected cases', function () {
        expect(ConnectionState::cases())->toHaveCount(4);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(ConnectionState::tryFrom('invalid'))->toBeNull();
    });
});
