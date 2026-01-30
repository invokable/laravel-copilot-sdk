<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\SessionStartHookInput;

describe('SessionStartHookInput', function () {
    it('can be created with all fields', function () {
        $input = new SessionStartHookInput(
            timestamp: 1706600000,
            cwd: '/home/user/project',
            source: 'startup',
            initialPrompt: 'Hello',
        );

        expect($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/home/user/project')
            ->and($input->source)->toBe('startup')
            ->and($input->initialPrompt)->toBe('Hello');
    });

    it('can be created with minimal fields', function () {
        $input = new SessionStartHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            source: 'new',
        );

        expect($input->source)->toBe('new')
            ->and($input->initialPrompt)->toBeNull();
    });

    it('can be created from array', function () {
        $input = SessionStartHookInput::fromArray([
            'timestamp' => 1706600000,
            'cwd' => '/var/www',
            'source' => 'resume',
            'initialPrompt' => 'Continue from before',
        ]);

        expect($input->source)->toBe('resume')
            ->and($input->initialPrompt)->toBe('Continue from before');
    });

    it('can be created from array with defaults', function () {
        $input = SessionStartHookInput::fromArray([]);

        expect($input->timestamp)->toBe(0)
            ->and($input->cwd)->toBe('')
            ->and($input->source)->toBe('new')
            ->and($input->initialPrompt)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $input = new SessionStartHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            source: 'startup',
            initialPrompt: 'Start prompt',
        );

        expect($input->toArray())->toBe([
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'source' => 'startup',
            'initialPrompt' => 'Start prompt',
        ]);
    });

    it('filters null values in toArray', function () {
        $input = new SessionStartHookInput(
            timestamp: 1706600000,
            cwd: '/tmp',
            source: 'new',
        );

        expect($input->toArray())->toBe([
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'source' => 'new',
        ]);
    });

    it('extends BaseHookInput', function () {
        $input = SessionStartHookInput::fromArray([]);

        expect($input)->toBeInstanceOf(\Revolution\Copilot\Types\Hooks\BaseHookInput::class);
    });
});
