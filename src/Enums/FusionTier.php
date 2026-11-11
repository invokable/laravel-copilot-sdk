<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Copilot Fusion tier, matching the CLI's `--fusion-tier`.
 *
 * The runtime resolves each tier to the Auto routing preference it stands for.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum FusionTier: string
{
    /** Copilot Fusion Standard, which balances capability, speed, and cost. */
    case STANDARD = 'standard';

    /** Copilot Fusion High. */
    case HIGH = 'high';

    /** Copilot Fusion Max, which prioritizes quality. */
    case MAX = 'max';
}
