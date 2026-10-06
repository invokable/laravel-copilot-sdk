<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\SubagentStartHookInput;

describe('SubagentStartHookInput', function () {
    it('round trips sub-agent start metadata', function () {
        $input = SubagentStartHookInput::fromArray([
            'sessionId' => 'parent-session',
            'timestamp' => 1706600000,
            'cwd' => '/workspace',
            'transcriptPath' => '/tmp/agent.jsonl',
            'agentName' => 'reviewer',
            'agentDisplayName' => 'Reviewer',
            'agentDescription' => 'Reviews code',
        ]);

        expect($input->toArray())->toBe([
            'sessionId' => 'parent-session',
            'timestamp' => 1706600000,
            'cwd' => '/workspace',
            'transcriptPath' => '/tmp/agent.jsonl',
            'agentName' => 'reviewer',
            'agentDisplayName' => 'Reviewer',
            'agentDescription' => 'Reviews code',
        ]);
    });

    it('omits optional metadata when absent', function () {
        $input = new SubagentStartHookInput(
            sessionId: 'parent-session',
            timestamp: 1,
            cwd: '/workspace',
            transcriptPath: '/tmp/agent.jsonl',
            agentName: 'reviewer',
        );

        expect($input->toArray())->toBe([
            'sessionId' => 'parent-session',
            'timestamp' => 1,
            'cwd' => '/workspace',
            'transcriptPath' => '/tmp/agent.jsonl',
            'agentName' => 'reviewer',
        ]);
    });
});
