<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\InstructionSourceLocation;

describe('InstructionSourceLocation', function () {
    it('has correct string values', function () {
        expect(InstructionSourceLocation::USER->value)->toBe('user')
            ->and(InstructionSourceLocation::REPOSITORY->value)->toBe('repository')
            ->and(InstructionSourceLocation::WORKING_DIRECTORY->value)->toBe('working-directory')
            ->and(InstructionSourceLocation::PLUGIN->value)->toBe('plugin');
    });

    it('can be created from string', function () {
        expect(InstructionSourceLocation::from('user'))->toBe(InstructionSourceLocation::USER)
            ->and(InstructionSourceLocation::from('working-directory'))->toBe(InstructionSourceLocation::WORKING_DIRECTORY)
            ->and(InstructionSourceLocation::from('plugin'))->toBe(InstructionSourceLocation::PLUGIN);
    });

    it('has all expected cases', function () {
        expect(InstructionSourceLocation::cases())->toHaveCount(4);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(InstructionSourceLocation::tryFrom('unknown'))->toBeNull();
    });
});
