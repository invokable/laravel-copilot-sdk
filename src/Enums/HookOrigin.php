<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum HookOrigin: string
{
    case User = 'user';
    case Repository = 'repository';
    case Plugin = 'plugin';
    case Policy = 'policy';
}
