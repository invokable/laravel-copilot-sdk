<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\InstructionSourceType;

describe('InstructionSourceType', function () {
    it('has correct string values', function () {
        expect(InstructionSourceType::HOME->value)->toBe('home')
            ->and(InstructionSourceType::REPO->value)->toBe('repo')
            ->and(InstructionSourceType::MODEL->value)->toBe('model')
            ->and(InstructionSourceType::VSCODE->value)->toBe('vscode')
            ->and(InstructionSourceType::NESTED_AGENTS->value)->toBe('nested-agents')
            ->and(InstructionSourceType::CHILD_INSTRUCTIONS->value)->toBe('child-instructions');
    });

    it('can be created from string', function () {
        expect(InstructionSourceType::from('home'))->toBe(InstructionSourceType::HOME)
            ->and(InstructionSourceType::from('repo'))->toBe(InstructionSourceType::REPO)
            ->and(InstructionSourceType::from('nested-agents'))->toBe(InstructionSourceType::NESTED_AGENTS);
    });

    it('has all expected cases', function () {
        expect(InstructionSourceType::cases())->toHaveCount(6);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(InstructionSourceType::tryFrom('unknown'))->toBeNull();
    });
});
