<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\SessionEndHookOutput;

describe('SessionEndHookOutput', function () {
    it('can be created with all fields', function () {
        $output = new SessionEndHookOutput(
            suppressOutput: true,
            cleanupActions: ['delete_temp', 'close_connections'],
            sessionSummary: 'Session completed 5 tasks',
        );

        expect($output->suppressOutput)->toBeTrue()
            ->and($output->cleanupActions)->toBe(['delete_temp', 'close_connections'])
            ->and($output->sessionSummary)->toBe('Session completed 5 tasks');
    });

    it('can be created with minimal fields', function () {
        $output = new SessionEndHookOutput;

        expect($output->suppressOutput)->toBeNull()
            ->and($output->cleanupActions)->toBeNull()
            ->and($output->sessionSummary)->toBeNull();
    });

    it('can be created from array', function () {
        $output = SessionEndHookOutput::fromArray([
            'suppressOutput' => false,
            'cleanupActions' => ['action1'],
            'sessionSummary' => 'Summary text',
        ]);

        expect($output->suppressOutput)->toBeFalse()
            ->and($output->cleanupActions)->toBe(['action1'])
            ->and($output->sessionSummary)->toBe('Summary text');
    });

    it('can convert to array with all fields', function () {
        $output = new SessionEndHookOutput(
            suppressOutput: true,
            cleanupActions: ['cleanup'],
            sessionSummary: 'Done',
        );

        expect($output->toArray())->toBe([
            'suppressOutput' => true,
            'cleanupActions' => ['cleanup'],
            'sessionSummary' => 'Done',
        ]);
    });

    it('filters null values in toArray', function () {
        $output = new SessionEndHookOutput(
            sessionSummary: 'Only summary',
        );

        expect($output->toArray())->toBe([
            'sessionSummary' => 'Only summary',
        ]);
    });

    it('implements Arrayable interface', function () {
        $output = new SessionEndHookOutput;

        expect($output)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
