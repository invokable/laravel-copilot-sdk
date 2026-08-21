<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a caller-supplied-card install-plan source.
 *
 * @experimental
 */
enum McpPlanInstallSourceCardKind: string
{
    /** Plan directly from a caller-supplied card. */
    case Card = 'card';
}
