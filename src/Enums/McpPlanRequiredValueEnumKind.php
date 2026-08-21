<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for an enumerated required value.
 *
 * @experimental
 */
enum McpPlanRequiredValueEnumKind: string
{
    /** The value uses a fixed non-empty enumeration. */
    case Enum = 'enum';
}
