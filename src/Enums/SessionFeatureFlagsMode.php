<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Assignment source used by the session feature-flag lifecycle.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum SessionFeatureFlagsMode: string
{
    case Local = 'local';

    case Cli = 'cli';
}
