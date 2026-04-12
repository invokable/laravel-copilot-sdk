<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentMode;

describe('AgentMode', function () {
    it('has correct string values', function () {
        expect(AgentMode::AUTOPILOT->value)->toBe('autopilot')
            ->and(AgentMode::INTERACTIVE->value)->toBe('interactive')
            ->and(AgentMode::PLAN->value)->toBe('plan')
            ->and(AgentMode::SHELL->value)->toBe('shell');
    });

    it('can be created from string', function () {
        expect(AgentMode::from('autopilot'))->toBe(AgentMode::AUTOPILOT)
            ->and(AgentMode::from('interactive'))->toBe(AgentMode::INTERACTIVE)
            ->and(AgentMode::from('plan'))->toBe(AgentMode::PLAN)
            ->and(AgentMode::from('shell'))->toBe(AgentMode::SHELL);
    });

    it('has all expected cases', function () {
        expect(AgentMode::cases())->toHaveCount(4);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(AgentMode::tryFrom('invalid'))->toBeNull();
    });
});
