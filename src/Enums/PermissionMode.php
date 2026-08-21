<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Permission mode for the session.
 *
 * @experimental
 */
enum PermissionMode: string
{
    /** Permission requests follow the normal approval flow. */
    case Manual = 'manual';

    /** Permission requests include an LLM safety recommendation; clients may automatically approve requests judged acceptable. */
    case Assisted = 'assisted';

    /** Tool, path, and URL permission requests are automatically approved. */
    case AllowAll = 'allow-all';
}
