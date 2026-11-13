<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Acquisition source used by a direct skill installation.
 *
 * @experimental
 */
enum SkillInstallSourceKind: string
{
    case Url = 'url';
    case File = 'file';
    case Directory = 'directory';
}
