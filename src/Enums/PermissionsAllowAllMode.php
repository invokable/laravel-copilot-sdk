<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Current or requested allow-all mode.
 *
 * @experimental
 */
enum PermissionsAllowAllMode: string
{
    /** Permission requests follow the normal approval flow with an LLM advisory recommendation attached. */
    case Auto = 'auto';

    /** Permission requests follow the normal approval flow. */
    case Off = 'off';

    /** Tool, path, and URL permission requests are automatically approved. */
    case On = 'on';
}
