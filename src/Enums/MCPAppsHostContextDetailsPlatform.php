<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Platform type for responsive design.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
enum MCPAppsHostContextDetailsPlatform: string
{
    case DESKTOP = 'desktop';
    case MOBILE = 'mobile';
    case WEB = 'web';
}
