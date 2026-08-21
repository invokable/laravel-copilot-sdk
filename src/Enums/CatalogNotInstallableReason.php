<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Why a discoverable candidate cannot be installed.
 *
 * @experimental
 */
enum CatalogNotInstallableReason: string
{
    /** This kind of resource is not installable through this surface. */
    case KindNotInstallable = 'kind-not-installable';

    /** AI skills are discoverable but have no typed importer in this phase. */
    case AiSkillNotInstallable = 'ai-skill-not-installable';

    /** Policy forbids installing this candidate. */
    case PolicyForbids = 'policy-forbids';
}
