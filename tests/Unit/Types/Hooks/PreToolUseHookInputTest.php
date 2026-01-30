<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\PreToolUseHookInput;

describe('PreToolUseHookInput', function () {
    it('can be created with all fields', function () {
        $input = new PreToolUseHookInput(
            timestamp: 1706600000,
            cwd: '/home/user/project',
            toolName: 'bash',
            toolArgs: ['command' => 'ls -la'],
        );

        expect($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/home/user/project')
            ->and($input->toolName)->toBe('bash')
            ->and($input->toolArgs)->toBe(['command' => 'ls -la']);
    });

    it('can be created from array', function () {
        $input = PreToolUseHookInput::fromArray([
            'timestamp' => 1706600000,
            'cwd' => '/var/www',
            'toolName' => 'edit',
            'toolArgs' => ['path' => '/file.txt', 'content' => 'test'],
        ]);

        expect($input->toolName)->toBe('edit')
            ->and($input->toolArgs)->toBe(['path' => '/file.txt', 'content' => 'test']);
    });

    it('can be created from array with defaults', function () {
        $input = PreToolUseHookInput::fromArray([]);

        expect($input->timestamp)->toBe(0)
            ->and($input->cwd)->toBe('')
            ->and($input->toolName)->toBe('')
            ->and($input->toolArgs)->toBeNull();
    });

    it('can convert to array', function () {
        $input = new PreToolUseHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            toolName: 'view',
            toolArgs: ['path' => '/test'],
        );

        expect($input->toArray())->toBe([
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'toolName' => 'view',
            'toolArgs' => ['path' => '/test'],
        ]);
    });

    it('extends BaseHookInput', function () {
        $input = new PreToolUseHookInput(
            timestamp: 0,
            cwd: '',
            toolName: '',
            toolArgs: null,
        );

        expect($input)->toBeInstanceOf(\Revolution\Copilot\Types\Hooks\BaseHookInput::class);
    });
});
