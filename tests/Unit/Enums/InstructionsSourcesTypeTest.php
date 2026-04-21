<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\InstructionsSourcesType;

describe('InstructionsSourcesType', function () {
    it('has correct string values', function () {
        expect(InstructionsSourcesType::HOME->value)->toBe('home')
            ->and(InstructionsSourcesType::REPO->value)->toBe('repo')
            ->and(InstructionsSourcesType::MODEL->value)->toBe('model')
            ->and(InstructionsSourcesType::VSCODE->value)->toBe('vscode')
            ->and(InstructionsSourcesType::NESTED_AGENTS->value)->toBe('nested-agents')
            ->and(InstructionsSourcesType::CHILD_INSTRUCTIONS->value)->toBe('child-instructions');
    });

    it('can be created from string', function () {
        expect(InstructionsSourcesType::from('home'))->toBe(InstructionsSourcesType::HOME)
            ->and(InstructionsSourcesType::from('nested-agents'))->toBe(InstructionsSourcesType::NESTED_AGENTS);
    });

    it('has all expected cases', function () {
        expect(InstructionsSourcesType::cases())->toHaveCount(6);
    });
});
