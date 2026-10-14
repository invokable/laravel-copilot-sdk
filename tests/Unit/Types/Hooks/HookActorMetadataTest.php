<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\AgentStopHookInput;
use Revolution\Copilot\Types\Hooks\BaseHookInput;
use Revolution\Copilot\Types\Hooks\ErrorOccurredHookInput;
use Revolution\Copilot\Types\Hooks\PostToolUseHookInput;
use Revolution\Copilot\Types\Hooks\PreMcpToolCallHookInput;
use Revolution\Copilot\Types\Hooks\PreToolUseHookInput;
use Revolution\Copilot\Types\Hooks\SessionEndHookInput;
use Revolution\Copilot\Types\Hooks\SessionStartHookInput;
use Revolution\Copilot\Types\Hooks\SubagentStartHookInput;
use Revolution\Copilot\Types\Hooks\SubagentStopHookInput;
use Revolution\Copilot\Types\Hooks\UserPromptSubmittedHookInput;
use Revolution\Copilot\Types\Hooks\UserPromptTransformedHookInput;

it('round trips actor metadata for hook inputs', function (string $inputClass, string $cwdKey) {
    $actorMetadata = [
        'agentId' => 'agent-1',
        'agentType' => 'worker',
        'parentAgentId' => 'agent-0',
    ];

    $input = $inputClass::fromArray([
        'sessionId' => 'session-1',
        'timestamp' => 1706600000,
        $cwdKey => '/workspace',
        ...$actorMetadata,
    ]);

    expect([$input->agentId, $input->agentType, $input->parentAgentId])
        ->toBe(array_values($actorMetadata))
        ->and(array_intersect_key($input->toArray(), $actorMetadata))
        ->toBe($actorMetadata);
})->with([
    'base input' => [BaseHookInput::class, 'cwd'],
    'agent stop' => [AgentStopHookInput::class, 'cwd'],
    'error occurred' => [ErrorOccurredHookInput::class, 'cwd'],
    'post tool use' => [PostToolUseHookInput::class, 'cwd'],
    'pre MCP tool call' => [PreMcpToolCallHookInput::class, 'workingDirectory'],
    'pre tool use' => [PreToolUseHookInput::class, 'cwd'],
    'session end' => [SessionEndHookInput::class, 'cwd'],
    'session start' => [SessionStartHookInput::class, 'cwd'],
    'subagent start' => [SubagentStartHookInput::class, 'cwd'],
    'subagent stop' => [SubagentStopHookInput::class, 'cwd'],
    'user prompt submitted' => [UserPromptSubmittedHookInput::class, 'cwd'],
    'user prompt transformed' => [UserPromptTransformedHookInput::class, 'cwd'],
]);

it('omits actor metadata when hooks do not include it', function (string $inputClass, string $cwdKey) {
    $input = $inputClass::fromArray([
        'sessionId' => 'session-1',
        'timestamp' => 1706600000,
        $cwdKey => '/workspace',
    ]);

    expect([$input->agentId, $input->agentType, $input->parentAgentId])
        ->toBe([null, null, null])
        ->and(array_intersect_key($input->toArray(), array_flip([
            'agentId',
            'agentType',
            'parentAgentId',
        ])))
        ->toBe([]);
})->with([
    'base input' => [BaseHookInput::class, 'cwd'],
    'agent stop' => [AgentStopHookInput::class, 'cwd'],
    'error occurred' => [ErrorOccurredHookInput::class, 'cwd'],
    'post tool use' => [PostToolUseHookInput::class, 'cwd'],
    'pre MCP tool call' => [PreMcpToolCallHookInput::class, 'workingDirectory'],
    'pre tool use' => [PreToolUseHookInput::class, 'cwd'],
    'session end' => [SessionEndHookInput::class, 'cwd'],
    'session start' => [SessionStartHookInput::class, 'cwd'],
    'subagent start' => [SubagentStartHookInput::class, 'cwd'],
    'subagent stop' => [SubagentStopHookInput::class, 'cwd'],
    'user prompt submitted' => [UserPromptSubmittedHookInput::class, 'cwd'],
    'user prompt transformed' => [UserPromptTransformedHookInput::class, 'cwd'],
]);
