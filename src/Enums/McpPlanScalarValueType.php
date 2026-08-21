<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Scalar type a required value must conform to.
 *
 * @experimental
 */
enum McpPlanScalarValueType: string
{
    /** Free text. */
    case String = 'string';

    /** A number. */
    case Number = 'number';

    /** A boolean. */
    case Boolean = 'boolean';

    /** A filesystem path. */
    case Path = 'path';
}
