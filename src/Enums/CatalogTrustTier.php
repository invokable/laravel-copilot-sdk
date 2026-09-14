<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Service-computed trust tier currently emitted by Agent Finder. It is independent of search
 * score, popularity, and client-side ranking.
 *
 * @experimental
 */
enum CatalogTrustTier: string
{
    /** Tier one as assigned by the catalogue authority. */
    case T1 = 'T1';

    /** Tier two as assigned by the catalogue authority. */
    case T2 = 'T2';
}
