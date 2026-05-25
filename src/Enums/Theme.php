<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Theme variant - UI theme preference per SEP-1865.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
enum Theme: string
{
    case DARK = 'dark';
    case LIGHT = 'light';
}
