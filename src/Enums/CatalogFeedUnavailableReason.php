<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Why a catalog feed entry cannot be presented.
 *
 * @experimental
 */
enum CatalogFeedUnavailableReason: string
{
    case NotFound = 'not-found';
    case UnsupportedKind = 'unsupported-kind';
    case InvalidResource = 'invalid-resource';
}
