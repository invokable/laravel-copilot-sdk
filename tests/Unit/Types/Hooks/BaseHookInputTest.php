<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Hooks\BaseHookInput;

describe('BaseHookInput', function () {
    it('can be created with all fields', function () {
        $input = new BaseHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            cwd: '/home/user/project',
            agentId: 'agent-1',
            agentType: 'worker',
            parentAgentId: 'agent-0',
        );

        expect($input->sessionId)->toBe('session-abc')
            ->and($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/home/user/project')
            ->and($input->agentId)->toBe('agent-1')
            ->and($input->agentType)->toBe('worker')
            ->and($input->parentAgentId)->toBe('agent-0');
    });

    it('can be created from array', function () {
        $input = BaseHookInput::fromArray([
            'sessionId' => 'session-xyz',
            'timestamp' => 1706600000,
            'cwd' => '/var/www',
            'agentId' => 'agent-2',
            'agentType' => 'worker',
            'parentAgentId' => 'agent-1',
        ]);

        expect($input->sessionId)->toBe('session-xyz')
            ->and($input->timestamp)->toBe(1706600000)
            ->and($input->cwd)->toBe('/var/www')
            ->and($input->agentId)->toBe('agent-2')
            ->and($input->agentType)->toBe('worker')
            ->and($input->parentAgentId)->toBe('agent-1');
    });

    it('can be created from array with defaults', function () {
        $input = BaseHookInput::fromArray([]);

        expect($input->sessionId)->toBe('')
            ->and($input->timestamp)->toBe(0)
            ->and($input->cwd)->toBe('')
            ->and($input->agentId)->toBeNull()
            ->and($input->agentType)->toBeNull()
            ->and($input->parentAgentId)->toBeNull();
    });

    it('can convert to array', function () {
        $input = new BaseHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            cwd: '/tmp',
            agentId: 'agent-1',
            agentType: 'worker',
            parentAgentId: 'agent-0',
        );

        expect($input->toArray())->toBe([
            'sessionId' => 'session-abc',
            'timestamp' => 1706600000,
            'cwd' => '/tmp',
            'agentId' => 'agent-1',
            'agentType' => 'worker',
            'parentAgentId' => 'agent-0',
        ]);
    });

    it('implements Arrayable interface', function () {
        $input = new BaseHookInput(sessionId: '', timestamp: 0, cwd: '');

        expect($input)->toBeInstanceOf(Arrayable::class);
    });
});
