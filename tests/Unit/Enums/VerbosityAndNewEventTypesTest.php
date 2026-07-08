<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Enums\Verbosity;

describe('Verbosity', function () {
    it('has correct string values', function () {
        expect(Verbosity::LOW->value)->toBe('low')
            ->and(Verbosity::MEDIUM->value)->toBe('medium')
            ->and(Verbosity::HIGH->value)->toBe('high');
    });

    it('can be created from string', function () {
        expect(Verbosity::from('low'))->toBe(Verbosity::LOW)
            ->and(Verbosity::from('medium'))->toBe(Verbosity::MEDIUM)
            ->and(Verbosity::from('high'))->toBe(Verbosity::HIGH);
    });

    it('has all expected cases', function () {
        expect(Verbosity::cases())->toHaveCount(3);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(Verbosity::tryFrom('unknown'))->toBeNull();
    });
});

describe('New SessionEventType cases (SDK sync)', function () {
    it('has assistant.tool_call_delta case', function () {
        expect(SessionEventType::ASSISTANT_TOOL_CALL_DELTA->value)->toBe('assistant.tool_call_delta');
    });

    it('can create assistant.tool_call_delta from string', function () {
        expect(SessionEventType::from('assistant.tool_call_delta'))->toBe(SessionEventType::ASSISTANT_TOOL_CALL_DELTA);
    });
});
