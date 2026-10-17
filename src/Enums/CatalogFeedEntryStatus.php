<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a catalog feed entry.
 *
 * @experimental
 */
enum CatalogFeedEntryStatus: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Filtered = 'filtered';
}
