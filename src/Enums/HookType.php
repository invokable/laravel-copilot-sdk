<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum HookType: string
{
    case PreToolUse = 'preToolUse';
    case PreMcpToolCall = 'preMcpToolCall';
    case PostToolUse = 'postToolUse';
    case PostToolUseFailure = 'postToolUseFailure';
    case UserPromptSubmitted = 'userPromptSubmitted';
    case UserPromptTransformed = 'userPromptTransformed';
    case SessionStart = 'sessionStart';
    case SessionEnd = 'sessionEnd';
    case PostResult = 'postResult';
    case PrePrDescription = 'prePRDescription';
    case ErrorOccurred = 'errorOccurred';
    case AgentStop = 'agentStop';
    case SubagentStart = 'subagentStart';
    case SubagentStop = 'subagentStop';
    case PreCompact = 'preCompact';
    case PermissionRequest = 'permissionRequest';
    case Notification = 'notification';
}
