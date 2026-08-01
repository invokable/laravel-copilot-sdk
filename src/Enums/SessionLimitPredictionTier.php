<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Semantic usage tier used for a recommended cap or additional headroom.
 *
 * @experimental This API is experimental and may change or be removed.
 */
enum SessionLimitPredictionTier: string
{
    /** Recommended starting tier. */
    case RECOMMENDED = 'recommended';

    /** Additional headroom for longer-running sessions. */
    case ADDITIONAL_HEADROOM = 'additional_headroom';

    /** Generous headroom for unusually high usage. */
    case GENEROUS_HEADROOM = 'generous_headroom';

    /** Maximum available headroom tier. */
    case MAXIMUM_HEADROOM = 'maximum_headroom';
}
