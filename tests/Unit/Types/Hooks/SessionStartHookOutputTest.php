<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\SessionStartHookOutput;

describe('SessionStartHookOutput', function () {
    it('can be created with all fields', function () {
        $output = new SessionStartHookOutput(
            additionalContext: 'Extra context',
            modifiedConfig: ['model' => 'gpt-5'],
        );

        expect($output->additionalContext)->toBe('Extra context')
            ->and($output->modifiedConfig)->toBe(['model' => 'gpt-5']);
    });

    it('can be created with minimal fields', function () {
        $output = new SessionStartHookOutput;

        expect($output->additionalContext)->toBeNull()
            ->and($output->modifiedConfig)->toBeNull();
    });

    it('can be created from array', function () {
        $output = SessionStartHookOutput::fromArray([
            'additionalContext' => 'Context info',
            'modifiedConfig' => ['streaming' => true],
        ]);

        expect($output->additionalContext)->toBe('Context info')
            ->and($output->modifiedConfig)->toBe(['streaming' => true]);
    });

    it('can convert to array with all fields', function () {
        $output = new SessionStartHookOutput(
            additionalContext: 'Context',
            modifiedConfig: ['key' => 'value'],
        );

        expect($output->toArray())->toBe([
            'additionalContext' => 'Context',
            'modifiedConfig' => ['key' => 'value'],
        ]);
    });

    it('filters null values in toArray', function () {
        $output = new SessionStartHookOutput(
            additionalContext: 'Only context',
        );

        expect($output->toArray())->toBe([
            'additionalContext' => 'Only context',
        ]);
    });

    it('implements Arrayable interface', function () {
        $output = new SessionStartHookOutput;

        expect($output)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
