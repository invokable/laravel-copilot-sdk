<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\PostToolUseHookInput;
use Revolution\Copilot\Types\ToolResultObject;

describe('PostToolUseHookInput', function () {
    it('can be created with all fields', function () {
        $toolResult = new ToolResultObject(
            textResultForLlm: 'Success',
            resultType: 'success',
        );

        $input = new PostToolUseHookInput(
            timestamp: 1706600000,
            cwd: '/home/user/project',
            toolName: 'bash',
            toolArgs: ['command' => 'ls'],
            toolResult: $toolResult,
        );

        expect($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/home/user/project')
            ->and($input->toolName)->toBe('bash')
            ->and($input->toolArgs)->toBe(['command' => 'ls'])
            ->and($input->toolResult)->toBe($toolResult);
    });

    it('can be created from array with toolResult as array', function () {
        $input = PostToolUseHookInput::fromArray([
            'timestamp' => 1706600000,
            'cwd' => '/var/www',
            'toolName' => 'edit',
            'toolArgs' => ['path' => '/file.txt'],
            'toolResult' => [
                'textResultForLlm' => 'File updated',
                'resultType' => 'success',
            ],
        ]);

        expect($input->toolName)->toBe('edit')
            ->and($input->toolResult)->toBeInstanceOf(ToolResultObject::class)
            ->and($input->toolResult->textResultForLlm)->toBe('File updated');
    });

    it('can be created from array with defaults', function () {
        $input = PostToolUseHookInput::fromArray([]);

        expect($input->timestamp)->toBe(0)
            ->and($input->cwd)->toBe('')
            ->and($input->toolName)->toBe('')
            ->and($input->toolArgs)->toBeNull()
            ->and($input->toolResult)->toBeInstanceOf(ToolResultObject::class);
    });

    it('can convert to array', function () {
        $toolResult = new ToolResultObject(
            textResultForLlm: 'Done',
            resultType: 'success',
        );

        $input = new PostToolUseHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            toolName: 'view',
            toolArgs: ['path' => '/test'],
            toolResult: $toolResult,
        );

        $array = $input->toArray();

        expect($array['timestamp'])->toBe(1706600000)
            ->and($array['cwd'])->toBe('/tmp')
            ->and($array['toolName'])->toBe('view')
            ->and($array['toolArgs'])->toBe(['path' => '/test'])
            ->and($array['toolResult'])->toBe([
                'textResultForLlm' => 'Done',
                'resultType' => 'success',
            ]);
    });

    it('extends BaseHookInput', function () {
        $input = PostToolUseHookInput::fromArray([]);

        expect($input)->toBeInstanceOf(\Revolution\Copilot\Types\Hooks\BaseHookInput::class);
    });
});
