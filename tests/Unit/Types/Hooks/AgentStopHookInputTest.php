<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\AgentStopHookInput;
use Revolution\Copilot\Types\Hooks\BaseHookInput;

describe('AgentStopHookInput', function () {
    it('can be created with all fields', function () {
        $input = new AgentStopHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            cwd: '/home/user/project',
            stopReason: 'end_turn',
            transcriptPath: '/tmp/transcript.json',
            stopHookActive: true,
        );

        expect($input->sessionId)->toBe('session-abc')
            ->and($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/home/user/project')
            ->and($input->stopReason)->toBe('end_turn')
            ->and($input->transcriptPath)->toBe('/tmp/transcript.json')
            ->and($input->stopHookActive)->toBeTrue();
    });

    it('can be created with default values', function () {
        $input = new AgentStopHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            cwd: '/tmp',
        );

        expect($input->stopReason)->toBeNull()
            ->and($input->transcriptPath)->toBeNull()
            ->and($input->stopHookActive)->toBeNull();
    });

    it('can be created from array', function () {
        $input = AgentStopHookInput::fromArray([
            'sessionId' => 'session-abc',
            'timestamp' => 1706600000,
            'cwd' => '/var/www',
            'stopReason' => 'end_turn',
            'transcriptPath' => '/tmp/transcript.json',
            'stopHookActive' => false,
        ]);

        expect($input->sessionId)->toBe('session-abc')
            ->and($input->stopReason)->toBe('end_turn')
            ->and($input->transcriptPath)->toBe('/tmp/transcript.json')
            ->and($input->stopHookActive)->toBeFalse();
    });

    it('can be created from array with defaults', function () {
        $input = AgentStopHookInput::fromArray([]);

        expect($input->sessionId)->toBe('')
            ->and($input->timestamp)->toBe(0)
            ->and($input->cwd)->toBe('')
            ->and($input->stopReason)->toBeNull()
            ->and($input->transcriptPath)->toBeNull()
            ->and($input->stopHookActive)->toBeNull();
    });

    it('can convert to array', function () {
        $input = new AgentStopHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            cwd: '/tmp',
            stopReason: 'end_turn',
            transcriptPath: '/tmp/transcript.json',
            stopHookActive: true,
        );

        expect($input->toArray())->toBe([
            'sessionId' => 'session-abc',
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'stopReason' => 'end_turn',
            'transcriptPath' => '/tmp/transcript.json',
            'stopHookActive' => true,
        ]);
    });

    it('omits null values when converting to array', function () {
        $input = new AgentStopHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            cwd: '/tmp',
        );

        expect($input->toArray())->toBe([
            'sessionId' => 'session-abc',
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
        ]);
    });

    it('extends BaseHookInput', function () {
        $input = AgentStopHookInput::fromArray([]);

        expect($input)->toBeInstanceOf(BaseHookInput::class);
    });
});
