<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Runtime-controlled routing state for an open canvas instance.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
enum CanvasInstanceAvailability: string
{
    case READY = 'ready';
    case STALE = 'stale';
}
