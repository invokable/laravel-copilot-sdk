<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\UserPromptSubmittedHookOutput;

describe('UserPromptSubmittedHookOutput', function () {
    it('can be created with all fields', function () {
        $output = new UserPromptSubmittedHookOutput(
            modifiedPrompt: 'Modified prompt',
            additionalContext: 'Extra context',
            suppressOutput: true,
        );

        expect($output->modifiedPrompt)->toBe('Modified prompt')
            ->and($output->additionalContext)->toBe('Extra context')
            ->and($output->suppressOutput)->toBeTrue();
    });

    it('can be created with minimal fields', function () {
        $output = new UserPromptSubmittedHookOutput;

        expect($output->modifiedPrompt)->toBeNull()
            ->and($output->additionalContext)->toBeNull()
            ->and($output->suppressOutput)->toBeNull();
    });

    it('can be created from array', function () {
        $output = UserPromptSubmittedHookOutput::fromArray([
            'modifiedPrompt' => 'Changed prompt',
            'additionalContext' => 'Context info',
        ]);

        expect($output->modifiedPrompt)->toBe('Changed prompt')
            ->and($output->additionalContext)->toBe('Context info');
    });

    it('can convert to array with all fields', function () {
        $output = new UserPromptSubmittedHookOutput(
            modifiedPrompt: 'New prompt',
            additionalContext: 'Context',
            suppressOutput: false,
        );

        expect($output->toArray())->toBe([
            'modifiedPrompt' => 'New prompt',
            'additionalContext' => 'Context',
            'suppressOutput' => false,
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

        expect($output)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
