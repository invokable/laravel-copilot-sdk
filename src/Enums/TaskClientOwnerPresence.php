<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum TaskClientOwnerPresence: string
{
    case Connected = 'connected';
    case Disconnected = 'disconnected';
}
