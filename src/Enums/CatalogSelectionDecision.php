<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Terminal outcome for a catalog selection interaction.
 *
 * @experimental
 */
enum CatalogSelectionDecision: string
{
    case Selected = 'selected';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case TimedOut = 'timed-out';
}
