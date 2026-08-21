<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Whether a planned configuration change would create or modify an entry.
 *
 * @experimental
 */
enum McpPlanConfigurationOperation: string
{
    /** Creates a configuration entry that does not exist yet. */
    case Add = 'add';

    /** Modifies a configuration entry that already exists. */
    case Update = 'update';
}
