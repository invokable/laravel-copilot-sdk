<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * How a session combines tool allowlists and denylists.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum OptionsUpdateToolFilterPrecedence: string
{
    /** The allowlist takes precedence and the denylist is ignored when both are set. */
    case Available = 'available';

    /** A tool must pass both the allowlist and denylist when both are set. */
    case Excluded = 'excluded';
}
