<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * How a reviewed plugin MCP server is reached.
 *
 * @experimental
 */
enum PluginInstallationMcpTransport: string
{
    case Remote = 'remote';
    case Local = 'local';
}
