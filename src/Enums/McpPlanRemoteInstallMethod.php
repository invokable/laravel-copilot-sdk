<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a remote-endpoint transport choice.
 *
 * @experimental
 */
enum McpPlanRemoteInstallMethod: string
{
    /** Connect to a remote endpoint. */
    case Remote = 'remote';
}
