<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Routing preference for sessions using the automatic model.
 *
 * `FAST` is an integrator-only latency preset and is not a first-party GitHub Copilot product preference.
 */
enum AutoTier: string
{
    /** Optimize for efficiency. */
    case EFFICIENCY = 'efficiency';

    /** Balance efficiency and intelligence. */
    case BALANCE = 'balance';

    /** Optimize for intelligence. */
    case INTELLIGENCE = 'intelligence';

    /** Integrator-only preset that optimizes for latency. */
    case FAST = 'fast';
}
