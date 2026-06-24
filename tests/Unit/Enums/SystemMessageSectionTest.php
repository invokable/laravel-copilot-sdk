<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SystemMessageSection;

describe('SystemMessageSection', function () {
    it('has correct string values', function () {
        expect(SystemMessageSection::IDENTITY->value)->toBe('identity')
            ->and(SystemMessageSection::TONE->value)->toBe('tone')
            ->and(SystemMessageSection::TOOL_EFFICIENCY->value)->toBe('tool_efficiency')
            ->and(SystemMessageSection::ENVIRONMENT_CONTEXT->value)->toBe('environment_context')
            ->and(SystemMessageSection::CODE_CHANGE_RULES->value)->toBe('code_change_rules')
            ->and(SystemMessageSection::GUIDELINES->value)->toBe('guidelines')
            ->and(SystemMessageSection::SAFETY->value)->toBe('safety')
            ->and(SystemMessageSection::TOOL_INSTRUCTIONS->value)->toBe('tool_instructions')
            ->and(SystemMessageSection::CUSTOM_INSTRUCTIONS->value)->toBe('custom_instructions')
            ->and(SystemMessageSection::RUNTIME_INSTRUCTIONS->value)->toBe('runtime_instructions')
            ->and(SystemMessageSection::LAST_INSTRUCTIONS->value)->toBe('last_instructions');
    });

    it('can be created from string', function () {
        expect(SystemMessageSection::from('identity'))->toBe(SystemMessageSection::IDENTITY)
            ->and(SystemMessageSection::from('runtime_instructions'))->toBe(SystemMessageSection::RUNTIME_INSTRUCTIONS)
            ->and(SystemMessageSection::from('last_instructions'))->toBe(SystemMessageSection::LAST_INSTRUCTIONS);
    });

    it('has all expected cases', function () {
        expect(SystemMessageSection::cases())->toHaveCount(12);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(SystemMessageSection::tryFrom('invalid_section'))->toBeNull();
    });

    it('provides descriptions for all sections', function () {
        $descriptions = SystemMessageSection::descriptions();

        expect($descriptions)->toBeArray()
            ->and($descriptions)->toHaveCount(12)
            ->and($descriptions['identity'])->toBeString()
            ->and($descriptions['runtime_instructions'])->toContain('Runtime-provided context')
            ->and($descriptions['last_instructions'])->toContain('End-of-prompt');
    });

    it('has description for each enum case', function () {
        $descriptions = SystemMessageSection::descriptions();

        foreach (SystemMessageSection::cases() as $case) {
            expect($descriptions)->toHaveKey($case->value);
        }
    });
});
