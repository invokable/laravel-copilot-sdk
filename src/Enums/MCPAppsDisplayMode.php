<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Current display mode (SEP-1865).
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
enum MCPAppsDisplayMode: string
{
    case FULLSCREEN = 'fullscreen';
    case INLINE = 'inline';
    case PIP = 'pip';
}
