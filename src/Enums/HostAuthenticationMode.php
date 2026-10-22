<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum HostAuthenticationMode: string
{
    case GitHub = 'github';
    case None = 'none';
    case Custom = 'custom';
}
