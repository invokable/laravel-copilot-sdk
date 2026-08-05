<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Effective extension loading and agent-management mode.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum DiscoveredExtensionMode: string
{
    case DISABLED = 'disabled';
    case LOAD_ONLY = 'load_only';
    case LOAD_AND_AUGMENT = 'load_and_augment';
}
