<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Planned resource kind or refusal for catalog reference installation.
 *
 * @experimental
 */
enum CatalogReferenceInstallationOutcomeKind: string
{
    case PluginInstallPlanned = 'plugin-install-planned';
    case Refused = 'refused';
    case SkillInstallPlanned = 'skill-install-planned';
}
