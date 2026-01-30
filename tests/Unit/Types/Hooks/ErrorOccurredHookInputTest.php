<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\ErrorOccurredHookInput;

describe('ErrorOccurredHookInput', function () {
    it('can be created with all fields', function () {
        $input = new ErrorOccurredHookInput(
            timestamp: 1706600000,
            cwd: '/home/user/project',
            error: 'Connection failed',
            errorContext: 'model_call',
            recoverable: true,
        );

        expect($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/home/user/project')
            ->and($input->error)->toBe('Connection failed')
            ->and($input->errorContext)->toBe('model_call')
            ->and($input->recoverable)->toBeTrue();
    });

    it('can be created with tool_execution context', function () {
        $input = new ErrorOccurredHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            error: 'Tool crashed',
            errorContext: 'tool_execution',
            recoverable: false,
        );

        expect($input->errorContext)->toBe('tool_execution')
            ->and($input->recoverable)->toBeFalse();
    });

    it('can be created from array', function () {
        $input = ErrorOccurredHookInput::fromArray([
            'timestamp' => 1706600000,
            'cwd' => '/var/www',
            'error' => 'System error',
            'errorContext' => 'system',
            'recoverable' => false,
        ]);

        expect($input->error)->toBe('System error')
            ->and($input->errorContext)->toBe('system')
            ->and($input->recoverable)->toBeFalse();
    });

    it('can be created from array with defaults', function () {
        $input = ErrorOccurredHookInput::fromArray([]);

        expect($input->timestamp)->toBe(0)
            ->and($input->cwd)->toBe('')
            ->and($input->error)->toBe('')
            ->and($input->errorContext)->toBe('system')
            ->and($input->recoverable)->toBeFalse();
    });

    it('can convert to array', function () {
        $input = new ErrorOccurredHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            error: 'User input error',
            errorContext: 'user_input',
            recoverable: true,
        );

        expect($input->toArray())->toBe([
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'error' => 'User input error',
            'errorContext' => 'user_input',
            'recoverable' => true,
        ]);
    });

    it('extends BaseHookInput', function () {
        $input = ErrorOccurredHookInput::fromArray([]);

        expect($input)->toBeInstanceOf(\Revolution\Copilot\Types\Hooks\BaseHookInput::class);
    });
});
