<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Hooks\AgentStopHookOutput;

describe('AgentStopHookOutput', function () {
    it('can be created with all fields', function () {
        $output = new AgentStopHookOutput(
            decision: 'block',
            reason: 'Findings need remediation',
        );

        expect($output->decision)->toBe('block')
            ->and($output->reason)->toBe('Findings need remediation');
    });

    it('can be created with default values', function () {
        $output = new AgentStopHookOutput;

        expect($output->decision)->toBeNull()
            ->and($output->reason)->toBeNull();
    });

    it('can be created from array', function () {
        $output = AgentStopHookOutput::fromArray([
            'decision' => 'block',
            'reason' => 'Please continue working',
        ]);

        expect($output->decision)->toBe('block')
            ->and($output->reason)->toBe('Please continue working');
    });

    it('can be created from empty array', function () {
        $output = AgentStopHookOutput::fromArray([]);

        expect($output->decision)->toBeNull()
            ->and($output->reason)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $output = new AgentStopHookOutput(
            decision: 'block',
            reason: 'Remediate findings',
        );

        expect($output->toArray())->toBe([
            'decision' => 'block',
            'reason' => 'Remediate findings',
        ]);
    });

    it('filters null values in toArray', function () {
        $output = new AgentStopHookOutput;

        expect($output->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        $output = new AgentStopHookOutput;

        expect($output)->toBeInstanceOf(Arrayable::class);
    });
});
