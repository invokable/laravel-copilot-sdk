<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\InstructionsSourcesLocation;

describe('InstructionsSourcesLocation', function () {
    it('has correct string values', function () {
        expect(InstructionsSourcesLocation::USER->value)->toBe('user')
            ->and(InstructionsSourcesLocation::REPOSITORY->value)->toBe('repository')
            ->and(InstructionsSourcesLocation::WORKING_DIRECTORY->value)->toBe('working-directory');
    });

    it('can be created from string', function () {
        expect(InstructionsSourcesLocation::from('user'))->toBe(InstructionsSourcesLocation::USER)
            ->and(InstructionsSourcesLocation::from('working-directory'))->toBe(InstructionsSourcesLocation::WORKING_DIRECTORY);
    });

    it('has all expected cases', function () {
        expect(InstructionsSourcesLocation::cases())->toHaveCount(3);
    });
});
