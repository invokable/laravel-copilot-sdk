<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\SessionEndHookInput;

describe('SessionEndHookInput', function () {
    it('can be created with all fields', function () {
        $input = new SessionEndHookInput(
            timestamp: 1706600000,
            cwd: '/home/user/project',
            reason: 'complete',
            finalMessage: 'Task completed successfully',
            error: null,
        );

        expect($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/home/user/project')
            ->and($input->reason)->toBe('complete')
            ->and($input->finalMessage)->toBe('Task completed successfully')
            ->and($input->error)->toBeNull();
    });

    it('can be created with error reason', function () {
        $input = new SessionEndHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            reason: 'error',
            error: 'Connection timeout',
        );

        expect($input->reason)->toBe('error')
            ->and($input->error)->toBe('Connection timeout');
    });

    it('can be created from array', function () {
        $input = SessionEndHookInput::fromArray([
            'timestamp' => 1706600000,
            'cwd' => '/var/www',
            'reason' => 'abort',
            'finalMessage' => 'User aborted',
        ]);

        expect($input->reason)->toBe('abort')
            ->and($input->finalMessage)->toBe('User aborted');
    });

    it('can be created from array with defaults', function () {
        $input = SessionEndHookInput::fromArray([]);

        expect($input->timestamp)->toBe(0)
            ->and($input->cwd)->toBe('')
            ->and($input->reason)->toBe('complete')
            ->and($input->finalMessage)->toBeNull()
            ->and($input->error)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $input = new SessionEndHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            reason: 'timeout',
            finalMessage: 'Timed out',
            error: 'Exceeded limit',
        );

        expect($input->toArray())->toBe([
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'reason' => 'timeout',
            'finalMessage' => 'Timed out',
            'error' => 'Exceeded limit',
        ]);
    });

    it('filters null values in toArray', function () {
        $input = new SessionEndHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            reason: 'user_exit',
        );

        expect($input->toArray())->toBe([
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'reason' => 'user_exit',
        ]);
    });

    it('extends BaseHookInput', function () {
        $input = SessionEndHookInput::fromArray([]);

        expect($input)->toBeInstanceOf(\Revolution\Copilot\Types\Hooks\BaseHookInput::class);
    });
});
