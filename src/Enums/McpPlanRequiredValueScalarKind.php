<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a scalar required value.
 *
 * @experimental
 */
enum McpPlanRequiredValueScalarKind: string
{
    /** The value uses one scalar type. */
    case Scalar = 'scalar';
}
