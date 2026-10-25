<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum SessionsOpenStatus: string
{
    case Created = 'created';
    case Resumed = 'resumed';
    case NotFound = 'not_found';
    case Connected = 'connected';
    case HandedOff = 'handed_off';
}
