<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\SubagentStartHookInput;
use Revolution\Copilot\Types\Hooks\SubagentStopHookInput;

describe('SubagentStopHookInput', function () {
    it('round trips the completed sub-agent response', function () {
        $input = SubagentStopHookInput::fromArray([
            'sessionId' => 'parent-session',
            'timestamp' => 1706600000,
            'cwd' => '/workspace',
            'transcriptPath' => '/tmp/agent.jsonl',
            'agentName' => 'reviewer',
            'agentId' => 'agent-1',
            'agentType' => 'general-purpose',
            'stopReason' => 'end_turn',
            'response' => 'Review complete',
        ]);

        expect($input)->toBeInstanceOf(SubagentStartHookInput::class)
            ->and($input->toArray())->toBe([
                'sessionId' => 'parent-session',
                'timestamp' => 1706600000,
                'cwd' => '/workspace',
                'transcriptPath' => '/tmp/agent.jsonl',
                'agentName' => 'reviewer',
                'agentId' => 'agent-1',
                'agentType' => 'general-purpose',
                'stopReason' => 'end_turn',
                'response' => 'Review complete',
            ]);
    });
});
