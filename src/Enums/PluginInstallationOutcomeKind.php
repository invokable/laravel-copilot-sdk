<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Terminal outcomes for verified Agent Plugin installation.
 *
 * @experimental
 */
enum PluginInstallationOutcomeKind: string
{
    case Cancelled = 'cancelled';
    case Declined = 'declined';
    case Installed = 'installed';
    case Refused = 'refused';
}
