<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum AgentToolCategoryId: string
{
    case Shell = 'shell';
    case Read = 'read';
    case Edit = 'edit';
    case Subagents = 'subagents';
    case Skills = 'skills';
    case Web = 'web';
    case AskUser = 'ask-user';
}
