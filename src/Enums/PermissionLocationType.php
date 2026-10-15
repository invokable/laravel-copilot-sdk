<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum PermissionLocationType: string
{
    case Repo = 'repo';
    case Dir = 'dir';
}
