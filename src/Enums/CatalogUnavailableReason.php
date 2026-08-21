<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Why a catalog operation is not available on this runtime.
 *
 * @experimental
 */
enum CatalogUnavailableReason: string
{
    /** Bounded search is not wired up on this runtime build. */
    case SearchUnavailable = 'search-unavailable';

    /** Install planning is not wired up on this runtime build. */
    case PlanningUnavailable = 'planning-unavailable';

    /** No catalog authority is configured for this runtime. */
    case AuthorityNotConfigured = 'authority-not-configured';

    /** The surface is disabled by policy on this runtime. */
    case DisabledByPolicy = 'disabled-by-policy';
}
