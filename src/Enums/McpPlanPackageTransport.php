<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Transport exposed by a locally launched package.
 *
 * @experimental
 */
enum McpPlanPackageTransport: string
{
    /** A locally launched process spoken to over standard input and output. */
    case Stdio = 'stdio';
}
