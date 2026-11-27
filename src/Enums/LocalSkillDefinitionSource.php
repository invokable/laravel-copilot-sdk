<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * @experimental
 */
enum LocalSkillDefinitionSource: string
{
    case Project = 'project';
    case Inherited = 'inherited';
    case PersonalCopilot = 'personal-copilot';
    case PersonalAgents = 'personal-agents';
    case Plugin = 'plugin';
    case Custom = 'custom';
    case Builtin = 'builtin';
}
