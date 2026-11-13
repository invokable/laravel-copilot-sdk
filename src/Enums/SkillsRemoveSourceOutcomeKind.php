<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Outcome variants for direct skill source removal.
 *
 * @experimental
 */
enum SkillsRemoveSourceOutcomeKind: string
{
    case NeedsInventory = 'needs-inventory';
    case Removed = 'removed';
}
