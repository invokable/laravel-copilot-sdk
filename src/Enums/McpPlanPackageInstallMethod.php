<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a package-backed transport choice.
 *
 * @experimental
 */
enum McpPlanPackageInstallMethod: string
{
    /** Install and run a local package. */
    case Package = 'package';
}
