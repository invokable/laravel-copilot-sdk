<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum TaskClientActiveStatus: string
{
    case Running = 'running';
    case Idle = 'idle';
}
