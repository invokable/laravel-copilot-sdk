<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Outcome variants for skill installation management.
 *
 * @experimental
 */
enum SkillInstallationManagementOutcomeKind: string
{
    case RecoveryRequired = 'recovery-required';
    case InstallPlanned = 'install-planned';
    case Listed = 'listed';
    case Recovered = 'recovered';
    case RolledBack = 'rolled-back';
    case UninstallPlanned = 'uninstall-planned';
    case Operation = 'operation';
    case EnabledChanged = 'enabled-changed';
    case Refused = 'refused';
}
