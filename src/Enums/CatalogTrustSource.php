<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Bounded authority that supplied a catalogue trust observation.
 *
 * @experimental
 */
enum CatalogTrustSource: string
{
    /** GitHub Agent Finder supplied the trust field on its search result. */
    case AgentFinder = 'agent-finder';
}
