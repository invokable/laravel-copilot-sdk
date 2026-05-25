<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * What triggered the skill invocation.
 */
enum SkillInvokedTrigger: string
{
    /** Skill invocation requested explicitly by the user, such as via a slash command or UI affordance. */
    case USER_INVOKED = 'user-invoked';

    /** Skill invocation requested by the agent. */
    case AGENT_INVOKED = 'agent-invoked';

    /** Skill content loaded as part of another context, such as a configured custom agent or subagent. */
    case CONTEXT_LOAD = 'context-load';
}
