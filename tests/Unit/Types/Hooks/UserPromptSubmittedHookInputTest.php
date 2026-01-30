<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\UserPromptSubmittedHookInput;

describe('UserPromptSubmittedHookInput', function () {
    it('can be created with all fields', function () {
        $input = new UserPromptSubmittedHookInput(
            timestamp: 1706600000,
            cwd: '/home/user/project',
            prompt: 'What is the meaning of life?',
        );

        expect($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/home/user/project')
            ->and($input->prompt)->toBe('What is the meaning of life?');
    });

    it('can be created from array', function () {
        $input = UserPromptSubmittedHookInput::fromArray([
            'timestamp' => 1706600000,
            'cwd' => '/var/www',
            'prompt' => 'Tell me a joke',
        ]);

        expect($input->prompt)->toBe('Tell me a joke');
    });

    it('can be created from array with defaults', function () {
        $input = UserPromptSubmittedHookInput::fromArray([]);

        expect($input->timestamp)->toBe(0)
            ->and($input->cwd)->toBe('')
            ->and($input->prompt)->toBe('');
    });

    it('can convert to array', function () {
        $input = new UserPromptSubmittedHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            prompt: 'Hello world',
        );

        expect($input->toArray())->toBe([
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'prompt' => 'Hello world',
        ]);
    });

    it('extends BaseHookInput', function () {
        $input = UserPromptSubmittedHookInput::fromArray([]);

        expect($input)->toBeInstanceOf(\Revolution\Copilot\Types\Hooks\BaseHookInput::class);
    });
});
