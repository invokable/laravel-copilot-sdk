<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\PostToolUseHookOutput;
use Revolution\Copilot\Types\ToolResultObject;

describe('PostToolUseHookOutput', function () {
    it('can be created with all fields', function () {
        $modifiedResult = new ToolResultObject(
            textResultForLlm: 'Modified result',
            resultType: 'success',
        );

        $output = new PostToolUseHookOutput(
            modifiedResult: $modifiedResult,
            additionalContext: 'Extra context',
            suppressOutput: true,
        );

        expect($output->modifiedResult)->toBe($modifiedResult)
            ->and($output->additionalContext)->toBe('Extra context')
            ->and($output->suppressOutput)->toBeTrue();
    });

    it('can be created with minimal fields', function () {
        $output = new PostToolUseHookOutput;

        expect($output->modifiedResult)->toBeNull()
            ->and($output->additionalContext)->toBeNull()
            ->and($output->suppressOutput)->toBeNull();
    });

    it('can be created from array with modifiedResult as array', function () {
        $output = PostToolUseHookOutput::fromArray([
            'modifiedResult' => [
                'textResultForLlm' => 'Changed output',
                'resultType' => 'success',
            ],
            'additionalContext' => 'Context info',
        ]);

        expect($output->modifiedResult)->toBeInstanceOf(ToolResultObject::class)
            ->and($output->modifiedResult->textResultForLlm)->toBe('Changed output')
            ->and($output->additionalContext)->toBe('Context info');
    });

    it('preserves ToolResultObject instance when passed directly', function () {
        $modifiedResult = new ToolResultObject(
            textResultForLlm: 'Direct result',
            resultType: 'success',
        );

        $output = PostToolUseHookOutput::fromArray([
            'modifiedResult' => $modifiedResult,
        ]);

        expect($output->modifiedResult)->toBe($modifiedResult);
    });

    it('can convert to array with all fields', function () {
        $modifiedResult = new ToolResultObject(
            textResultForLlm: 'Result',
            resultType: 'success',
        );

        $output = new PostToolUseHookOutput(
            modifiedResult: $modifiedResult,
            additionalContext: 'Context',
            suppressOutput: false,
        );

        expect($output->toArray())->toBe([
            'modifiedResult' => [
                'textResultForLlm' => 'Result',
                'resultType' => 'success',
            ],
            'additionalContext' => 'Context',
            'suppressOutput' => false,
        ]);
    });

    it('filters null values in toArray', function () {
        $output = new PostToolUseHookOutput(
            additionalContext: 'Only context',
        );

        expect($output->toArray())->toBe([
            'additionalContext' => 'Only context',
        ]);
    });

    it('implements Arrayable interface', function () {
        $output = new PostToolUseHookOutput;

        expect($output)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
