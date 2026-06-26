<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Runtime-controlled routing state for an open canvas instance.
 *
 * @deprecated This enum was removed in the official SDK. Kept for backward compatibility.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
enum CanvasInstanceAvailability: string
{
    case READY = 'ready';
    case STALE = 'stale';
}
