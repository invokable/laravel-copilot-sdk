<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Context tier override for matching subagents.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum SubagentSettingsEntryContextTier: string
{
    /** Inherit the parent session's effective context tier at dispatch time. */
    case Inherit = 'inherit';
    /** Use the model's default context window. */
    case Default = 'default';
    /** Pin the subagent to the long-context tier when supported. */
    case LongContext = 'long_context';
}
