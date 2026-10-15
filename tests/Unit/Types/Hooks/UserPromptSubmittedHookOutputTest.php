<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Hooks\UserPromptSubmittedHookOutput;

describe('UserPromptSubmittedHookOutput', function () {
    it('can be created with all fields', function () {
        $output = new UserPromptSubmittedHookOutput(
            modifiedPrompt: 'Modified prompt',
            additionalContext: 'Extra context',
            suppressOutput: true,
            decision: 'block',
            reason: 'Prompt rejected by policy.',
        );

        expect($output->modifiedPrompt)->toBe('Modified prompt')
            ->and($output->additionalContext)->toBe('Extra context')
            ->and($output->suppressOutput)->toBeTrue()
            ->and($output->decision)->toBe('block')
            ->and($output->reason)->toBe('Prompt rejected by policy.');
    });

    it('can be created with minimal fields', function () {
        $output = new UserPromptSubmittedHookOutput;

        expect($output->modifiedPrompt)->toBeNull()
            ->and($output->additionalContext)->toBeNull()
            ->and($output->suppressOutput)->toBeNull()
            ->and($output->decision)->toBeNull()
            ->and($output->reason)->toBeNull();
    });

    it('can be created from array', function () {
        $output = UserPromptSubmittedHookOutput::fromArray([
            'modifiedPrompt' => 'Changed prompt',
            'additionalContext' => 'Context info',
            'decision' => 'block',
            'reason' => 'Prompt rejected.',
        ]);

        expect($output->modifiedPrompt)->toBe('Changed prompt')
            ->and($output->additionalContext)->toBe('Context info')
            ->and($output->decision)->toBe('block')
            ->and($output->reason)->toBe('Prompt rejected.');
    });

    it('can convert to array with all fields', function () {
        $output = new UserPromptSubmittedHookOutput(
            modifiedPrompt: 'New prompt',
            additionalContext: 'Context',
            suppressOutput: false,
            decision: 'block',
            reason: 'Prompt rejected.',
        );

        expect($output->toArray())->toBe([
            'modifiedPrompt' => 'New prompt',
            'additionalContext' => 'Context',
            'suppressOutput' => false,
            'decision' => 'block',
            'reason' => 'Prompt rejected.',
        ]);
    });

    it('filters null values in toArray', function () {
        $output = new UserPromptSubmittedHookOutput(
            modifiedPrompt: 'Only prompt',
        );

        expect($output->toArray())->toBe([
            'modifiedPrompt' => 'Only prompt',
        ]);
    });

    it('implements Arrayable interface', function () {
        $output = new UserPromptSubmittedHookOutput;

        expect($output)->toBeInstanceOf(Arrayable::class);
    });
});
