<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Session model context tier.
 */
enum SessionModelChangeDataContextTier: string
{
    /** Default context tier with standard context window size. */
    case DEFAULT = 'default';

    /** Extended context tier with a larger context window. */
    case LONG_CONTEXT = 'long_context';
}
