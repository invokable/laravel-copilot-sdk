<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Consumer allowed to call an MCP tool.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum McpToolUiVisibility: string
{
    /** The model may call the tool. */
    case Model = 'model';

    /** An MCP App view may call the tool. */
    case App = 'app';
}
