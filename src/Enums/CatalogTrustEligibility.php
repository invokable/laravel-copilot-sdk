<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Authority-computed exposure eligibility, kept separate from tier. The current tier-only Agent
 * Finder response maps to `unknown`, never to a locally inferred eligibility.
 *
 * @experimental
 */
enum CatalogTrustEligibility: string
{
    /** Eligible for default catalogue exposure. */
    case Default = 'default';

    /** Eligible only when expanded or community results are requested. */
    case Expanded = 'expanded';

    /** Not eligible for normal catalogue exposure. */
    case Hidden = 'hidden';

    /** The authority did not supply an eligibility decision. */
    case Unknown = 'unknown';
}
