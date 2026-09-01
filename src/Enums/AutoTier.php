<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Routing preference for sessions using the automatic model.
 */
enum AutoTier: string
{
    /** Optimize for efficiency. */
    case EFFICIENCY = 'efficiency';

    /** Balance efficiency and intelligence. */
    case BALANCE = 'balance';

    /** Optimize for intelligence. */
    case INTELLIGENCE = 'intelligence';
}
