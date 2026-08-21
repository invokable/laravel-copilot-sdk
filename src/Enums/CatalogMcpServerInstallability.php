<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Whether an MCP server candidate can be planned for installation.
 *
 * @experimental
 */
enum CatalogMcpServerInstallability: string
{
    /** An install plan can be computed for this MCP server candidate. */
    case Installable = 'installable';

    /** Policy forbids installing this MCP server candidate. */
    case NotInstallablePolicy = 'not-installable-policy';
}
