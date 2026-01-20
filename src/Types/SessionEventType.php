<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

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
    case SESSION_INFO = 'session.info';
    case SESSION_MODEL_CHANGE = 'session.model_change';
    case SESSION_HANDOFF = 'session.handoff';
    case SESSION_TRUNCATION = 'session.truncation';
    case SESSION_USAGE_INFO = 'session.usage_info';
    case SESSION_COMPACTION_START = 'session.compaction_start';
    case SESSION_COMPACTION_COMPLETE = 'session.compaction_complete';

    // User messages
    case USER_MESSAGE = 'user.message';
    case PENDING_MESSAGES_MODIFIED = 'pending_messages.modified';

    // Assistant events
    case ASSISTANT_TURN_START = 'assistant.turn_start';
    case ASSISTANT_INTENT = 'assistant.intent';
    case ASSISTANT_REASONING = 'assistant.reasoning';
    case ASSISTANT_REASONING_DELTA = 'assistant.reasoning_delta';
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
    case TOOL_EXECUTION_COMPLETE = 'tool.execution_complete';

    // Subagent events
    case SUBAGENT_STARTED = 'subagent.started';
    case SUBAGENT_COMPLETED = 'subagent.completed';
    case SUBAGENT_FAILED = 'subagent.failed';
    case SUBAGENT_SELECTED = 'subagent.selected';

    // Hook events
    case HOOK_START = 'hook.start';
    case HOOK_END = 'hook.end';

    // System message
    case SYSTEM_MESSAGE = 'system.message';
}
