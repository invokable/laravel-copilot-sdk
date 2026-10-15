<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Result variants for verified Agent Plugin installation planning.
 *
 * @experimental
 */
enum PluginInstallationManagementOutcomeKind: string
{
    case InstallPlanned = 'install-planned';
    case Refused = 'refused';
}
