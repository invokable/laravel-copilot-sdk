<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum UIAutoModeSwitchResponse: string
{
    case Yes = 'yes';
    case YesAlways = 'yes_always';
    case No = 'no';
}
