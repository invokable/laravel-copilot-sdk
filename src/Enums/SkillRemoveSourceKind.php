<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Resource removed by a direct-source operation.
 *
 * @experimental
 */
enum SkillRemoveSourceKind: string
{
    case Directory = 'directory';
    case Personal = 'personal';
    case Project = 'project';
}
