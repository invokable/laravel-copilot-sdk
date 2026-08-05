<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Source of a persisted discovered extension.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum DiscoveredExtensionSource: string
{
    case USER = 'user';
    case PLUGIN = 'plugin';
}
