<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Controls if tools provided by an MCP server can be loaded on demand (auto) or always
 * included in the initial tool list (never).
 */
enum McpServerConfigDeferTools: string
{
    /** Tools may be deferred under certain conditions. */
    case Auto = 'auto';
    /** Tools are always included in the initial tool list, even when tool search is enabled. */
    case Never = 'never';
}
