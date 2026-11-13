<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Destination scope for a directly installed skill file.
 *
 * @experimental
 */
enum SkillInstallSourceScope: string
{
    case Personal = 'personal';
    case Project = 'project';
}
