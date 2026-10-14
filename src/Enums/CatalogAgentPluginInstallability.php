<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Whether an Agent Plugin candidate can be installed in the selected session.
 *
 * @experimental
 */
enum CatalogAgentPluginInstallability: string
{
    case Installable = 'installable';

    case InstallDataUnavailable = 'install-data-unavailable';

    case FeatureDisabled = 'feature-disabled';

    case NotInstallableKind = 'not-installable-kind';
}
