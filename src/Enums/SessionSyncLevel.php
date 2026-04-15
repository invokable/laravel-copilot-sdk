<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum SessionSyncLevel: string
{
    case LOCAL = 'local';
    case USER = 'user';
    case REPO_AND_USER = 'repo_and_user';
}
