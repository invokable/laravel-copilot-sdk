<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for an enumerated required value type.
 *
 * @experimental
 */
enum McpPlanEnumValueType: string
{
    /** One of a fixed, non-empty set of permitted values. */
    case Enum = 'enum';
}
