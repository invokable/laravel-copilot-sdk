<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum SessionsOpenHandoffTaskType: string
{
    case Cca = 'cca';
    case Cli = 'cli';
}
