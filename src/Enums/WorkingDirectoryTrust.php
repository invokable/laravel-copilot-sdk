<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum WorkingDirectoryTrust: string
{
    case Session = 'session';

    case User = 'user';
}
