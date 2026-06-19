<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Which tier this skill discovery directory belongs to.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum SkillDiscoveryScope: string
{
    case PROJECT = 'project';
    case PERSONAL_COPILOT = 'personal-copilot';
    case PERSONAL_AGENTS = 'personal-agents';
    case CUSTOM = 'custom';
}
