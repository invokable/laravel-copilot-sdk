<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\PreToolUseHookOutput;

describe('PreToolUseHookOutput', function () {
    it('can be created with all fields', function () {
        $output = new PreToolUseHookOutput(
            permissionDecision: 'allow',
            permissionDecisionReason: 'User approved',
            modifiedArgs: ['modified' => true],
            additionalContext: 'Extra context',
            suppressOutput: true,
        );

        expect($output->permissionDecision)->toBe('allow')
            ->and($output->permissionDecisionReason)->toBe('User approved')
            ->and($output->modifiedArgs)->toBe(['modified' => true])
            ->and($output->additionalContext)->toBe('Extra context')
            ->and($output->suppressOutput)->toBeTrue();
    });

    it('can be created with minimal fields', function () {
        $output = new PreToolUseHookOutput;

        expect($output->permissionDecision)->toBeNull()
            ->and($output->permissionDecisionReason)->toBeNull()
            ->and($output->modifiedArgs)->toBeNull()
            ->and($output->additionalContext)->toBeNull()
            ->and($output->suppressOutput)->toBeNull();
    });

    it('can be created with deny decision', function () {
        $output = new PreToolUseHookOutput(
            permissionDecision: 'deny',
            permissionDecisionReason: 'Security policy',
        );

        expect($output->permissionDecision)->toBe('deny')
            ->and($output->permissionDecisionReason)->toBe('Security policy');
    });

    it('can be created from array', function () {
        $output = PreToolUseHookOutput::fromArray([
            'permissionDecision' => 'ask',
            'additionalContext' => 'Need confirmation',
        ]);

        expect($output->permissionDecision)->toBe('ask')
            ->and($output->additionalContext)->toBe('Need confirmation');
    });

    it('can convert to array with all fields', function () {
        $output = new PreToolUseHookOutput(
            permissionDecision: 'allow',
            permissionDecisionReason: 'Approved',
            modifiedArgs: ['key' => 'value'],
            additionalContext: 'Context',
            suppressOutput: false,
        );

        expect($output->toArray())->toBe([
            'permissionDecision' => 'allow',
            'permissionDecisionReason' => 'Approved',
            'modifiedArgs' => ['key' => 'value'],
            'additionalContext' => 'Context',
            'suppressOutput' => false,
        ]);
    });

    it('filters null values in toArray', function () {
        $output = new PreToolUseHookOutput(
            permissionDecision: 'allow',
        );

        expect($output->toArray())->toBe([
            'permissionDecision' => 'allow',
        ]);
    });

    it('implements Arrayable interface', function () {
        $output = new PreToolUseHookOutput;

        expect($output)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
