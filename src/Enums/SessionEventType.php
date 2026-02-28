<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Session event types from Copilot CLI.
 */
enum SessionEventType: string
{
    // Session lifecycle
    case SESSION_START = 'session.start';
    case SESSION_RESUME = 'session.resume';
    case SESSION_ERROR = 'session.error';
    case SESSION_IDLE = 'session.idle';
    case SESSION_TITLE_CHANGED = 'session.title_changed';
    case SESSION_INFO = 'session.info';
    case SESSION_WARNING = 'session.warning';
    case SESSION_MODEL_CHANGE = 'session.model_change';
    case SESSION_MODE_CHANGED = 'session.mode_changed';
    case SESSION_PLAN_CHANGED = 'session.plan_changed';
    case SESSION_WORKSPACE_FILE_CHANGED = 'session.workspace_file_changed';
    case SESSION_HANDOFF = 'session.handoff';
    case SESSION_TRUNCATION = 'session.truncation';
    case SESSION_SNAPSHOT_REWIND = 'session.snapshot_rewind';
    case SESSION_SHUTDOWN = 'session.shutdown';
    case SESSION_USAGE_INFO = 'session.usage_info';
    case SESSION_COMPACTION_START = 'session.compaction_start';
    case SESSION_COMPACTION_COMPLETE = 'session.compaction_complete';
    case SESSION_CONTEXT_CHANGED = 'session.context_changed';
    case SESSION_TASK_COMPLETE = 'session.task_complete';

    // User messages
    case USER_MESSAGE = 'user.message';
    case PENDING_MESSAGES_MODIFIED = 'pending_messages.modified';

    // Assistant events
    case ASSISTANT_TURN_START = 'assistant.turn_start';
    case ASSISTANT_INTENT = 'assistant.intent';
    case ASSISTANT_REASONING = 'assistant.reasoning';
    case ASSISTANT_REASONING_DELTA = 'assistant.reasoning_delta';
    case ASSISTANT_STREAMING_DELTA = 'assistant.streaming_delta';
    case ASSISTANT_MESSAGE = 'assistant.message';
    case ASSISTANT_MESSAGE_DELTA = 'assistant.message_delta';
    case ASSISTANT_TURN_END = 'assistant.turn_end';
    case ASSISTANT_USAGE = 'assistant.usage';

    // Abort
    case ABORT = 'abort';

    // Tool events
    case TOOL_USER_REQUESTED = 'tool.user_requested';
    case TOOL_EXECUTION_START = 'tool.execution_start';
    case TOOL_EXECUTION_PARTIAL_RESULT = 'tool.execution_partial_result';
    case TOOL_EXECUTION_PROGRESS = 'tool.execution_progress';
    case TOOL_EXECUTION_COMPLETE = 'tool.execution_complete';

    // Skill events
    case SKILL_INVOKED = 'skill.invoked';

    // Subagent events
    case SUBAGENT_STARTED = 'subagent.started';
    case SUBAGENT_COMPLETED = 'subagent.completed';
    case SUBAGENT_FAILED = 'subagent.failed';
    case SUBAGENT_SELECTED = 'subagent.selected';
    case SUBAGENT_DESELECTED = 'subagent.deselected';

    // Hook events
    case HOOK_START = 'hook.start';
    case HOOK_END = 'hook.end';

    // System message
    case SYSTEM_MESSAGE = 'system.message';
}
