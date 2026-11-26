<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum SpawnCwdValidationReason: string
{
    case CwdNotFound = 'cwd-not-found';
    case CwdNotDirectory = 'cwd-not-directory';
}
