<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum RuntimeConnectionKind: string
{
    case STDIO = 'stdio';
    case TCP = 'tcp';
    case URI = 'uri';
}
