<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum EntraTokenInteraction: string
{
    case SILENT = 'silent';
    case INTERACTIVE = 'interactive';
    case FORCE_INTERACTIVE = 'force-interactive';
}
